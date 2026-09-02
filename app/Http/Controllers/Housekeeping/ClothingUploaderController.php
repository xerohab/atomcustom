<?php

namespace App\Http\Controllers\Housekeeping;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClothingUploaderController extends Controller
{
    private const PERMISSION = 'manage_clothing_uploader';

    private const MAX_FILES = 100;
    private const MAX_FILE_KB = 16384;

    private function guard(): void
    {
        $perm = DB::table('housekeeping_permissions')
            ->where('permission', self::PERMISSION)
            ->first();

        $minRank = $perm ? (int) $perm->min_rank : 6;

        abort_unless(
            auth()->check() &&
            (int) auth()->user()->rank >= $minRank,
            403
        );
    }

    private function ensureInstalled(): void
    {
        if (
            !Schema::hasTable('clothing_upload_batches') ||
            !Schema::hasTable('clothing_uploads')
        ) {
            abort(
                503,
                'Clothing uploader database tables are not installed.'
            );
        }
    }

    public function index(Request $request)
    {
        $this->guard();
        $this->ensureInstalled();

        $pages = DB::table('catalog_pages')
            ->select(
                'id',
                'parent_id',
                'caption',
                'caption_save',
                'min_rank',
                'visible',
                'enabled'
            )
            ->where('enabled', '1')
            ->orderBy('caption')
            ->orderBy('id')
            ->get();

        $recentBatches = DB::table('clothing_upload_batches')
            ->where('user_id', auth()->id())
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        $selectedBatch = null;
        $jobs = collect();

        if ($request->filled('batch')) {
            $selectedBatch = DB::table('clothing_upload_batches')
                ->where('id', (int) $request->input('batch'))
                ->first();

            if ($selectedBatch) {
                $jobs = DB::table('clothing_uploads')
                    ->where('batch_id', $selectedBatch->id)
                    ->orderBy('id')
                    ->get();
            }
        }

        return view(
            'housekeeping.clothing-uploader',
            compact(
                'pages',
                'recentBatches',
                'selectedBatch',
                'jobs'
            )
        );
    }

    public function store(Request $request)
    {
        $this->guard();
        $this->ensureInstalled();

        $request->validate([
            'clothing' => 'required|array|min:1|max:' . self::MAX_FILES,
            'clothing.*' => 'required|file|max:' . self::MAX_FILE_KB,

            'page_id' => 'required|integer|min:1',

            'credits' => 'required|integer|min:0|max:2147483647',
            'points' => 'required|integer|min:0|max:2147483647',
            'points_type' => 'required|integer|min:0|max:2147483647',

            'gender' => 'required|in:auto,M,F,U',
        ]);

        $pageId = (int) $request->input('page_id');

        $page = DB::table('catalog_pages')
            ->where('id', $pageId)
            ->where('enabled', '1')
            ->first();

        if (!$page) {
            throw ValidationException::withMessages([
                'page_id' => 'Choose a valid enabled catalogue page.',
            ]);
        }

        $files = $request->file('clothing', []);

        if (count($files) > self::MAX_FILES) {
            throw ValidationException::withMessages([
                'clothing' => 'Too many clothing files in one batch.',
            ]);
        }

        /*
         * Validate ALL files before moving the first file.
         */
        $prepared = [];

        foreach ($files as $index => $file) {
            $source = $this->detectFileType($file->getRealPath());

            if (!$source) {
                throw ValidationException::withMessages([
                    "clothing.$index" =>
                        $file->getClientOriginalName() .
                        ' is not a recognised SWF or Nitro bundle.',
                ]);
            }

            $base = pathinfo(
                $file->getClientOriginalName(),
                PATHINFO_FILENAME
            );

            $prepared[$index] = [
                'source' => $source,
                'base' => $base,
            ];
        }

        $inbox = rtrim(
            env(
                'CLOTHING_UPLOAD_INBOX',
                storage_path('app/clothing-uploader/inbox')
            ),
            '/'
        );

        if (
            !is_dir($inbox) &&
            !mkdir($inbox, 0770, true) &&
            !is_dir($inbox)
        ) {
            abort(500, 'Could not create clothing uploader inbox.');
        }

        $moved = [];

        try {
            $batchId = DB::transaction(function () use (
                $request,
                $files,
                $prepared,
                $page,
                $pageId,
                $inbox,
                &$moved
            ) {
                $batchId = DB::table(
                    'clothing_upload_batches'
                )->insertGetId([
                    'user_id' => auth()->id(),
                    'username' => (string) auth()->user()->username,

                    'target_page_id' => $pageId,
                    'target_page_caption' => (string) $page->caption,

                    'file_count' => count($files),

                    'price_credits' =>
                        (int) $request->input('credits'),

                    'price_points' =>
                        (int) $request->input('points'),

                    'points_type' =>
                        (int) $request->input('points_type'),

                    'created_at' => now(),
                ]);

                foreach ($files as $index => $file) {
                    $source = $prepared[$index]['source'];
                    $base = $prepared[$index]['base'];

                    $storedAs =
                        Str::uuid()->toString() .
                        '.' .
                        $source;

                    $file->move($inbox, $storedAs);

                    $moved[] =
                        $inbox .
                        '/' .
                        $storedAs;

                    DB::table('clothing_uploads')->insert([
                        'batch_id' => $batchId,

                        'user_id' => auth()->id(),
                        'username' =>
                            (string) auth()->user()->username,

                        'original_name' =>
                            $file->getClientOriginalName(),

                        'stored_as' => $storedAs,
                        'source' => $source,

                        'requested_name' =>
                            mb_substr($this->displayName($base), 0, 120),

                        'gender' =>
                            (string) $request->input('gender'),

                        'catalog_page' => $pageId,

                        'price_credits' =>
                            (int) $request->input('credits'),

                        'price_points' =>
                            (int) $request->input('points'),

                        'points_type' =>
                            (int) $request->input('points_type'),

                        'status' => 'waiting',

                        'message' =>
                            'Queued. Waiting for clothing worker.',

                        'created_at' => now(),
                    ]);
                }

                return $batchId;
            });
        } catch (\Throwable $e) {
            foreach ($moved as $path) {
                if (is_file($path)) {
                    @unlink($path);
                }
            }

            throw $e;
        }

        return redirect()
            ->route(
                'housekeeping.clothing-uploader',
                ['batch' => $batchId]
            )
            ->with(
                'success',
                'Clothing batch uploaded and queued successfully.'
            );
    }

    public function batchStatus(int $batch)
    {
        $this->guard();
        $this->ensureInstalled();

        $jobs = DB::table('clothing_uploads')
            ->where('batch_id', $batch)
            ->orderBy('id')
            ->get([
                'id',
                'original_name',
                'source',
                'library_name',
                'gender',
                'figure_type',
                'status',
                'message',
                'set_id',
                'wrapper_id',
                'wrapper_name',
                'catalog_clothing_id',
                'catalog_item_id',
                'installed_nitro',
                'started_at',
                'finished_at',
            ]);

        return response()->json([
            'jobs' => $jobs,
        ]);
    }

    private function detectFileType(string $path): ?string
    {
        $handle = @fopen($path, 'rb');

        if (!$handle) {
            return null;
        }

        $head = fread($handle, 12);
        fclose($handle);

        if (
            strlen($head) >= 3 &&
            in_array(
                substr($head, 0, 3),
                ['FWS', 'CWS', 'ZWS'],
                true
            )
        ) {
            return 'swf';
        }

        /*
         * Nitro bundle:
         *
         * uint16 file count
         * uint16 filename length
         * filename
         * uint32 packed data length
         *
         * We only perform a lightweight envelope check here.
         * The worker performs the strict parser/validation.
         */
        $size = @filesize($path);

        if ($size === false || $size < 8) {
            return null;
        }

        $handle = @fopen($path, 'rb');

        if (!$handle) {
            return null;
        }

        $countRaw = fread($handle, 2);

        if (strlen($countRaw) !== 2) {
            fclose($handle);
            return null;
        }

        $count = unpack('n', $countRaw)[1];

        if ($count < 1 || $count > 128) {
            fclose($handle);
            return null;
        }

        $nameLenRaw = fread($handle, 2);

        if (strlen($nameLenRaw) !== 2) {
            fclose($handle);
            return null;
        }

        $nameLen = unpack('n', $nameLenRaw)[1];

        if ($nameLen < 1 || $nameLen > 1024) {
            fclose($handle);
            return null;
        }

        $name = fread($handle, $nameLen);

        fclose($handle);

        if (
            strlen($name) !== $nameLen ||
            preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $name)
        ) {
            return null;
        }

        return 'nitro';
    }

    private function displayName(string $name): string
    {
        $name = preg_replace(
            '/^(shirt|jacket|hair|hat|trousers|shoes|acc_[a-z]+|misc)_[MFU]_/i',
            '',
            $name
        );

        $name = preg_replace(
            '/([a-z0-9])([A-Z])/',
            '$1 $2',
            (string) $name
        );

        $name = str_replace(
            ['_', '-'],
            ' ',
            (string) $name
        );

        $name = preg_replace(
            '/\s+/',
            ' ',
            (string) $name
        );

        $name = trim((string) $name);

        return $name !== ''
            ? ucwords($name)
            : 'Custom Clothing';
    }
}
