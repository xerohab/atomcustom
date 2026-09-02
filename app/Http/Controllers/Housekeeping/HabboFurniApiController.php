<?php

namespace App\Http\Controllers\Housekeeping;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HabboFurniApiController extends Controller
{
    private const PERMISSION = 'manage_furniture_uploader';
    private const IMPORT_CHUNK_MAX = 10;
    private const API_PAGE_SIZE = 100;
    private const SWF_MAX_BYTES = 16 * 1024 * 1024;
    private const ICON_MAX_BYTES = 2 * 1024 * 1024;

    private function guard(): void
    {
        $perm = DB::table('housekeeping_permissions')->where('permission', self::PERMISSION)->first();
        $minRank = $perm ? (int) $perm->min_rank : 6;
        abort_unless(auth()->check() && (int) auth()->user()->rank >= $minRank, 403);
    }

    private function ensureInstalled(): void
    {
        if (!Schema::hasTable('furni_upload_batches') || !Schema::hasTable('furni_uploads')) {
            abort(503, 'Furniture uploader tables are not installed.');
        }
    }

    private function apiToken(): string
    {
        $token = trim((string) env('HABBOFURNI_API_TOKEN', ''));
        if ($token === '') {
            abort(503, 'HABBOFURNI_API_TOKEN is not configured in AtomCMS .env.');
        }
        return $token;
    }

    private function apiBase(): string
    {
        return rtrim((string) env('HABBOFURNI_API_URL', 'https://habbofurni.com/api/v1'), '/');
    }

    public function index()
    {
        $this->guard();
        $this->ensureInstalled();
        $this->apiToken();

        $defaultHotel = max(1, min(10, (int) env('HABBOFURNI_HOTEL_ID', 1)));
        $categories = [];
        $types = [];
        $apiWarning = null;

        try {
            $categories = array_values(array_filter(
                $this->simpleList('/furniture/categories', $defaultHotel),
                fn ($value) => !$this->isClothingLabel((string) $value)
            ));
            $types = array_values(array_filter(
                $this->simpleList('/furniture/types', $defaultHotel),
                fn ($value) => !$this->isClothingLabel((string) $value)
            ));
        } catch (\Throwable $e) {
            $apiWarning = 'The API settings are loaded, but categories/types could not be fetched yet: ' . $e->getMessage();
        }

        return view('housekeeping.habbo-furni-api', compact('defaultHotel', 'categories', 'types', 'apiWarning'));
    }

    public function scan(Request $request)
    {
        $this->guard();
        $this->ensureInstalled();

        $validated = $request->validate([
            'page' => 'required|integer|min:1|max:100000',
            'hotel_id' => 'required|integer|min:1|max:10',
            'search' => 'nullable|string|max:120',
            'category' => 'nullable|string|max:120',
            'type' => 'nullable|string|max:120',
            'revision' => 'nullable|string|max:50',
        ]);

        $query = [
            'page' => (int) $validated['page'],
            'per_page' => self::API_PAGE_SIZE,
        ];
        foreach (['search', 'category', 'type', 'revision'] as $key) {
            $value = trim((string) ($validated[$key] ?? ''));
            if ($value !== '') $query[$key] = $value;
        }

        $payload = $this->apiGet('/furniture', (int) $validated['hotel_id'], $query);
        $apiRows = is_array($payload['data'] ?? null) ? $payload['data'] : [];

        // Keep every API record in the scan and tag likely clothing/wearables.
        // The Housekeeping page can then hide/show them without relying on a
        // perfect server-side clothing detector.
        $clothingDetected = 0;
        $rows = [];
        foreach ($apiRows as $row) {
            if (!is_array($row)) continue;
            if ($this->isClothingFurniture($row)) $clothingDetected++;
            $rows[] = $row;
        }

        $classNames = [];
        foreach ($rows as $row) {
            $class = trim((string) ($row['classname'] ?? ''));
            if ($class !== '') $classNames[] = $class;
        }

        $installed = DB::table('items_base')
            ->whereIn('item_name', array_values(array_unique($classNames)))
            ->pluck('id', 'item_name')
            ->map(fn ($v) => (int) $v)
            ->all();

        $pending = DB::table('furni_uploads')
            ->whereIn('class_name', array_values(array_unique($classNames)))
            ->whereIn('status', ['waiting', 'running'])
            ->pluck('status', 'class_name')
            ->all();

        /*
         * Reconcile against FurnitureData without decoding the entire
         * ~59k-entry JSON document into PHP memory.
         *
         * The API page contains at most 100 classnames, so stream the file and
         * count occurrences only for those classnames. This keeps scanner
         * memory usage essentially constant even as FurnitureData grows.
         */
        $furnidataCounts = array_fill_keys(
            array_values(array_unique($classNames)),
            0
        );

        $furnidataPath = '/var/www/gamedata/config/FurnitureData.json';

        if (is_file($furnidataPath) && is_readable($furnidataPath)) {
            $handle = fopen($furnidataPath, 'rb');

            if ($handle !== false) {
                $buffer = '';

                while (!feof($handle)) {
                    $chunk = fread($handle, 1024 * 1024);

                    if ($chunk === false) {
                        break;
                    }

                    $buffer .= $chunk;

                    /*
                     * Match complete classname JSON properties. Process only
                     * complete matches and retain a small tail for a property
                     * split across two fread() chunks.
                     */
                    if (preg_match_all(
                        '/"classname"\\s*:\\s*"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"/',
                        $buffer,
                        $matches,
                        PREG_OFFSET_CAPTURE
                    )) {
                        $lastEnd = 0;

                        foreach ($matches[0] as $index => $wholeMatch) {
                            $encodedClass = $matches[1][$index][0] ?? '';
                            $decodedClass = json_decode('"' . $encodedClass . '"');

                            if (is_string($decodedClass) && array_key_exists($decodedClass, $furnidataCounts)) {
                                /*
                                 * We only care about 0, 1 or >1, so stop
                                 * increasing a classname once it is known to
                                 * be duplicated.
                                 */
                                if ($furnidataCounts[$decodedClass] < 2) {
                                    $furnidataCounts[$decodedClass]++;
                                }
                            }

                            $lastEnd = $wholeMatch[1] + strlen($wholeMatch[0]);
                        }

                        if ($lastEnd > 0) {
                            $buffer = substr($buffer, $lastEnd);
                        }
                    }

                    /*
                     * If a long stretch contains no classname field, retaining
                     * the whole chunk would defeat the streaming design.
                     * A classname/property is far smaller than 4096 bytes.
                     */
                    if (strlen($buffer) > 4096) {
                        $buffer = substr($buffer, -4096);
                    }
                }

                fclose($handle);
            }
        }

        $bundleDir = '/var/www/gamedata/furniture';

        $items = [];
        foreach ($rows as $row) {
            $class = trim((string) ($row['classname'] ?? ''));
            if ($class === '') continue;
            $hotelData = is_array($row['hotelData'] ?? null) ? $row['hotelData'] : [];
            $swfData = is_array($row['swf_data'] ?? null) ? $row['swf_data'] : [];

            $status = 'missing';
            $localId = null;
            $localProblem = null;

            if (isset($installed[$class])) {
                $status = 'installed';
                $localId = (int) $installed[$class];
            } elseif (isset($pending[$class])) {
                $status = 'queued';
            } else {
                $fdCount = (int) ($furnidataCounts[$class] ?? 0);

                /*
                 * FurnitureData duplicates are unsafe to auto-repair.
                 * Keep them out of the normal import selection.
                 */
                if ($fdCount > 1) {
                    $status = 'furnidata_duplicate';
                    $localProblem = 'Classname occurs more than once in FurnitureData.json.';
                } else {
                    /*
                     * Numbered Habbo variants intentionally share their base
                     * Nitro asset, e.g. bc_panel*43 -> bc_panel.nitro.
                     *
                     * Therefore only treat an existing bundle as a bundle-only
                     * conflict when the furniture is NOT a numbered variant.
                     */
                    $assetClass = preg_replace('/\\*[0-9]+$/', '', $class);
                    $isVariant = $assetClass !== $class;

                    $bundlePath = rtrim($bundleDir, '/') . '/' . $assetClass . '.nitro';

                    if (!$isVariant && is_file($bundlePath)) {
                        $status = 'bundle_only';
                        $localProblem = 'A Nitro bundle already exists locally but items_base does not contain this classname.';
                    }
                }

                /*
                 * An otherwise-missing furniture record is only importable when
                 * HabboFurni says a SWF source actually exists.
                 *
                 * Keep local FurnitureData/bundle conflicts as the higher-priority
                 * status so existing reconciliation behaviour is unchanged.
                 *
                 * The import endpoint still performs the stricter detail check
                 * (including requiring a non-empty SWF URL) before queueing.
                 */
                if ($status === 'missing' && !(bool) data_get($hotelData, 'swf.exists', false)) {
                    $status = 'missing_source';
                    $localProblem = 'HabboFurni does not provide a SWF source for this furniture.';
                }
            }

            $items[] = [
                'classname' => $class,
                'name' => (string) ($hotelData['name'] ?? $this->smartDisplayName($class)),
                'description' => (string) ($hotelData['description'] ?? ''),
                'type' => (string) ($hotelData['type'] ?? ''),
                'category' => (string) ($hotelData['category'] ?? ''),
                'furni_line' => (string) ($hotelData['furni_line'] ?? ''),
                'revision' => $hotelData['revision'] ?? null,
                'xdim' => $hotelData['xdim'] ?? ($swfData['xdim'] ?? null),
                'ydim' => $hotelData['ydim'] ?? ($swfData['ydim'] ?? null),
                'zdim' => $swfData['zdim'] ?? null,
                'icon_url' => data_get($hotelData, 'icon.url'),
                'swf_exists' => (bool) data_get($hotelData, 'swf.exists', false),
                'is_clothing' => $this->isClothingFurniture($row),
                'status' => $status,
                'local_id' => $localId,
                'local_problem' => $localProblem,
            ];
        }

        return response()->json([
            'items' => $items,
            'hotel' => $payload['hotel'] ?? null,
            'clothing_detected' => $clothingDetected,
            'meta' => $payload['meta'] ?? [
                'current_page' => (int) $validated['page'],
                'last_page' => (int) $validated['page'],
                'per_page' => self::API_PAGE_SIZE,
                'total' => count($items),
            ],
        ]);
    }

    public function import(Request $request)
    {
        $this->guard();
        $this->ensureInstalled();

        $validated = $request->validate([
            'hotel_id' => 'required|integer|min:1|max:10',
            'class_names' => 'required|array|min:1|max:' . self::IMPORT_CHUNK_MAX,
            'class_names.*' => ['required', 'string', 'max:120'],
            'price_credits' => 'required|integer|min:0|max:2147483647',
            'price_points' => 'required|integer|min:0|max:2147483647',
            'points_type' => 'required|integer|min:0|max:2147483647',
            'amount' => 'required|integer|min:1|max:1000',
            'give_inventory' => 'nullable|boolean',
            'catalog_target' => 'required|in:furniture,clothing',
        ]);

        $classNames = array_values(array_unique(array_filter(array_map(function ($value) {
            $value = trim((string) $value);

            // HabboFurni contains legitimate classnames that can start with numbers
            // or contain characters such as hyphens/dots. Do not apply the manual
            // uploader's stricter classname regex here. We only reject values that
            // could break a URL/path or contain control characters.
            if ($value === '' || strlen($value) > 120) {
                return null;
            }
            if (str_contains($value, '/') || str_contains($value, '\\') || str_contains($value, '..')) {
                return null;
            }
            if (preg_match('/[\x00-\x1F\x7F]/', $value)) {
                return null;
            }

            return $value;
        }, $validated['class_names']))));

        if (!$classNames) {
            return response()->json([
                'message' => 'None of the selected HabboFurni classnames were valid for import.',
                'queued' => [],
                'skipped' => [],
                'errors' => [],
            ], 422);
        }

        $hotelId = (int) $validated['hotel_id'];

        $alreadyInstalled = DB::table('items_base')->whereIn('item_name', $classNames)->pluck('item_name')->all();
        $alreadyPending = DB::table('furni_uploads')
            ->whereIn('class_name', $classNames)
            ->whereIn('status', ['waiting', 'running'])
            ->pluck('class_name')->all();
        $skip = array_flip(array_merge($alreadyInstalled, $alreadyPending));

        $inbox = rtrim((string) env('FURNI_UPLOAD_INBOX', storage_path('app/furniture-uploader/inbox')), '/');
        if (!is_dir($inbox) && !mkdir($inbox, 0770, true) && !is_dir($inbox)) {
            abort(500, 'Could not create furniture uploader inbox.');
        }

        $prepared = [];
        $createdFiles = [];
        $errors = [];
        $skipped = [];

        try {
            foreach ($classNames as $className) {
                if (isset($skip[$className])) {
                    $skipped[] = $className;
                    continue;
                }

                try {
                    $payload = $this->apiGet('/furniture/' . rawurlencode($className), $hotelId);
                    $row = $this->singleFurnitureRow($payload);
                    if (!$row) throw new \RuntimeException('Furniture was not returned by the API.');

                    $apiClass = trim((string) ($row['classname'] ?? ''));
                    if ($apiClass !== $className) {
                        throw new \RuntimeException('API classname did not match the requested classname.');
                    }

                    $hotelData = is_array($row['hotelData'] ?? null) ? $row['hotelData'] : [];
                    $swfData = is_array($row['swf_data'] ?? null) ? $row['swf_data'] : [];

                    $swfUrl = (string) data_get($hotelData, 'swf.url', '');
                    if (!(bool) data_get($hotelData, 'swf.exists', false) || $swfUrl === '') {
                        throw new \RuntimeException('HabboFurni does not provide a SWF for this furniture.');
                    }

                    $storedAs = Str::uuid()->toString() . '.swf';
                    $swfPath = $inbox . '/' . $storedAs;
                    $this->downloadAsset($swfUrl, $swfPath, self::SWF_MAX_BYTES, 'swf');
                    $createdFiles[] = $swfPath;

                    $iconStoredAs = null;
                    $iconUrl = (string) data_get($hotelData, 'icon.url', '');
                    if ((bool) data_get($hotelData, 'icon.exists', false) && $iconUrl !== '') {
                        try {
                            $iconStoredAs = Str::uuid()->toString() . '.png';
                            $iconPath = $inbox . '/' . $iconStoredAs;
                            $this->downloadAsset($iconUrl, $iconPath, self::ICON_MAX_BYTES, 'png');
                            $createdFiles[] = $iconPath;
                        } catch (\Throwable $iconError) {
                            $iconStoredAs = null;
                        }
                    }

                    $kind = strtolower((string) ($hotelData['type'] ?? '')) === 'wall' ? 'i' : 's';
                    $width = max(1, min(20, (int) ($hotelData['xdim'] ?? $swfData['xdim'] ?? 1)));
                    $length = max(1, min(20, (int) ($hotelData['ydim'] ?? $swfData['ydim'] ?? 1)));
                    $stackHeight = (float) ($swfData['zdim'] ?? 1.0);
                    if ($stackHeight < 0 || $stackHeight > 50) $stackHeight = 1.0;

                    $prepared[] = [
                        'class_name' => $className,
                        'display_name' => mb_substr(trim((string) ($hotelData['name'] ?? '')) ?: $this->smartDisplayName($className), 0, 120),
                        'description' => mb_substr((string) ($hotelData['description'] ?? ''), 0, 255),
                        'kind' => $kind,
                        'width' => $width,
                        'length' => $length,
                        'stack_height' => $stackHeight,
                        'allow_stack' => $this->remoteBool($hotelData, ['canstandon', 'can_stand_on', 'canstand', 'stackable'], true),
                        'allow_sit' => $this->remoteBool($hotelData, ['cansiton', 'can_sit_on', 'cansit'], false),
                        'allow_lay' => $this->remoteBool($hotelData, ['canlayon', 'can_lay_on', 'canlay'], false),
                        'allow_walk' => $this->remoteBool($hotelData, ['canstandon', 'can_stand_on', 'canwalk'], false),
                        'folder' => $this->folderName($hotelData),
                        'is_clothing' => $this->isClothingFurniture($row),
                        'stored_as' => $storedAs,
                        'icon_stored_as' => $iconStoredAs,
                    ];
                } catch (\Throwable $e) {
                    $errors[] = ['classname' => $className, 'message' => $e->getMessage()];
                }
            }

            if (!$prepared) {
                foreach ($createdFiles as $path) if (is_file($path)) @unlink($path);
                return response()->json(['queued' => [], 'skipped' => $skipped, 'errors' => $errors, 'batch_id' => null]);
            }

            $batchId = DB::transaction(function () use ($prepared, $validated, $hotelId) {
                $catalogTarget = (string) $validated['catalog_target'];
                $isClothingTarget = $catalogTarget === 'clothing';
                $rootPage = $isClothingTarget ? $this->ensureApiClothingRootPage() : $this->ensureApiRootPage();
                $batchId = DB::table('furni_upload_batches')->insertGetId([
                    'user_id' => auth()->id(),
                    'username' => (string) auth()->user()->username,
                    'target_page_id' => $rootPage,
                    'target_page_caption' => $isClothingTarget ? '[API] Hidden Clothing' : '[API] HabboFurni',
                    'file_count' => count($prepared),
                    'created_at' => now(),
                ]);

                foreach ($prepared as $item) {
                    $pageId = $isClothingTarget
                        ? $this->ensureApiClothingChildPage($rootPage, $item['folder'])
                        : $this->ensureApiChildPage($rootPage, $item['folder']);
                    DB::table('furni_uploads')->insert([
                        'batch_id' => $batchId,
                        'user_id' => auth()->id(),
                        'username' => (string) auth()->user()->username,
                        'original_name' => $item['class_name'] . '.swf',
                        'class_name' => $item['class_name'],
                        'display_name' => $item['display_name'],
                        'description' => $item['description'],
                        'kind' => $item['kind'],
                        'width' => $item['width'],
                        'length' => $item['length'],
                        'stack_height' => $item['stack_height'],
                        'allow_stack' => $item['allow_stack'],
                        'allow_sit' => $item['allow_sit'],
                        'allow_lay' => $item['allow_lay'],
                        'allow_walk' => $item['allow_walk'],
                        'catalog_page' => $pageId,
                        'price_credits' => (int) $validated['price_credits'],
                        'price_points' => (int) $validated['price_points'],
                        'points_type' => (int) $validated['points_type'],
                        'amount' => (int) $validated['amount'],
                        'give_inventory' => (bool) ($validated['give_inventory'] ?? false),
                        'icon_mode' => $item['icon_stored_as'] ? 'upload' : 'auto',
                        'icon_stored_as' => $item['icon_stored_as'],
                        'stored_as' => $item['stored_as'],
                        'source' => 'swf',
                        'status' => 'waiting',
                        'created_at' => now(),
                    ]);
                }

                return $batchId;
            });

            return response()->json([
                'batch_id' => $batchId,
                'queued' => array_column($prepared, 'class_name'),
                'skipped' => $skipped,
                'errors' => $errors,
            ]);
        } catch (\Throwable $e) {
            foreach ($createdFiles as $path) if (is_file($path)) @unlink($path);
            throw $e;
        }
    }

    public function status(Request $request)
    {
        $this->guard();
        $this->ensureInstalled();

        $limit = max(10, min(250, (int) $request->query('limit', 100)));

        $select = [
            'u.id',
            'u.batch_id',
            'u.original_name',
            'u.class_name',
            'u.display_name',
            'u.catalog_page',
            'u.status',
            'u.message',
            'u.assigned_id',
            'u.catalog_item_id',
            'u.created_at',
            'b.target_page_caption as batch_caption',
        ];

        if (Schema::hasColumn('furni_uploads', 'started_at')) {
            $select[] = 'u.started_at';
        }
        if (Schema::hasColumn('furni_uploads', 'finished_at')) {
            $select[] = 'u.finished_at';
        }

        $jobs = DB::table('furni_uploads as u')
            ->join('furni_upload_batches as b', 'b.id', '=', 'u.batch_id')
            ->where('b.target_page_caption', 'like', '[API]%')
            ->select($select)
            ->orderByDesc('u.id')
            ->limit($limit)
            ->get();

        $rows = $jobs->map(function ($job) {
            return [
                'id' => (int) $job->id,
                'batch_id' => (int) $job->batch_id,
                'batch_caption' => (string) ($job->batch_caption ?? ''),
                'original_name' => (string) ($job->original_name ?? ''),
                'class_name' => (string) ($job->class_name ?? ''),
                'display_name' => (string) ($job->display_name ?? ''),
                'catalog_page' => isset($job->catalog_page) ? (int) $job->catalog_page : null,
                'status' => strtolower((string) ($job->status ?? 'waiting')),
                'message' => (string) ($job->message ?? ''),
                'assigned_id' => isset($job->assigned_id) ? (int) $job->assigned_id : null,
                'catalog_item_id' => isset($job->catalog_item_id) ? (int) $job->catalog_item_id : null,
                'created_at' => isset($job->created_at) ? (string) $job->created_at : null,
                'started_at' => isset($job->started_at) ? (string) $job->started_at : null,
                'finished_at' => isset($job->finished_at) ? (string) $job->finished_at : null,
            ];
        })->values();

        $counts = ['total' => $rows->count(), 'waiting' => 0, 'running' => 0, 'done' => 0, 'error' => 0, 'other' => 0];
        foreach ($rows as $row) {
            $status = $row['status'];
            if (array_key_exists($status, $counts) && $status !== 'total') {
                $counts[$status]++;
            } else {
                $counts['other']++;
            }
        }

        return response()->json([
            'jobs' => $rows,
            'counts' => $counts,
            'refreshed_at' => now()->toIso8601String(),
        ]);
    }

    private function apiGet(string $path, int $hotelId, array $query = []): array
    {
        $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken(),
                'X-Hotel-ID' => (string) $hotelId,
                'Accept' => 'application/json',
            ])
            ->connectTimeout(10)
            ->timeout(30)
            ->retry(2, 400)
            ->get($this->apiBase() . $path, $query);

        if (!$response->successful()) {
            throw new \RuntimeException('HabboFurni API returned HTTP ' . $response->status() . '.');
        }
        $json = $response->json();
        if (!is_array($json)) throw new \RuntimeException('HabboFurni returned invalid JSON.');
        return $json;
    }

    private function simpleList(string $path, int $hotelId): array
    {
        $payload = $this->apiGet($path, $hotelId);
        $data = $payload['data'] ?? $payload;
        if (!is_array($data)) return [];
        $out = [];
        foreach ($data as $entry) {
            if (is_string($entry)) $out[] = $entry;
            elseif (is_array($entry)) {
                $value = $entry['name'] ?? $entry['value'] ?? $entry['type'] ?? $entry['category'] ?? null;
                if (is_string($value) && $value !== '') $out[] = $value;
            }
        }
        return array_values(array_unique($out));
    }

    private function singleFurnitureRow(array $payload): ?array
    {
        $data = $payload['data'] ?? $payload;
        if (isset($data['classname'])) return $data;
        if (is_array($data) && isset($data[0]) && is_array($data[0])) return $data[0];
        return null;
    }

    private function downloadAsset(string $url, string $target, int $maxBytes, string $kind): void
    {
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if ($scheme !== 'https' || !($host === 'habbofurni.com' || str_ends_with($host, '.habbofurni.com'))) {
            throw new \RuntimeException('Refused an unexpected asset host from the API.');
        }

        $response = Http::connectTimeout(10)->timeout(45)->retry(2, 500)->get($url);
        if (!$response->successful()) throw new \RuntimeException('Asset download returned HTTP ' . $response->status() . '.');
        $body = $response->body();
        if ($body === '' || strlen($body) > $maxBytes) throw new \RuntimeException('Downloaded asset was empty or too large.');

        if ($kind === 'swf' && !in_array(substr($body, 0, 3), ['FWS', 'CWS', 'ZWS'], true)) {
            throw new \RuntimeException('Downloaded furniture file is not a valid SWF.');
        }
        if ($kind === 'png' && substr($body, 0, 8) !== "\x89PNG\r\n\x1a\n") {
            throw new \RuntimeException('Downloaded icon is not a PNG.');
        }

        $tmp = $target . '.part-' . Str::random(8);
        if (file_put_contents($tmp, $body, LOCK_EX) === false) throw new \RuntimeException('Could not write downloaded asset to the uploader inbox.');
        @chmod($tmp, 0660);
        if (!@rename($tmp, $target)) {
            @unlink($tmp);
            throw new \RuntimeException('Could not move downloaded asset into the uploader inbox.');
        }
    }

    private function ensureApiRootPage(): int
    {
        $existing = DB::table('catalog_pages')->where('parent_id', -1)->where('caption', 'API Furniture')->value('id');
        if ($existing) return (int) $existing;
        return $this->createCataloguePage(-1, 'API Furniture', 1, true);
    }

    private function ensureApiChildPage(int $rootPage, string $caption): int
    {
        $existing = DB::table('catalog_pages')->where('parent_id', $rootPage)->where('caption', $caption)->value('id');
        if ($existing) return (int) $existing;
        return $this->createCataloguePage($rootPage, $caption, 1, true);
    }

    private function ensureApiClothingRootPage(): int
    {
        $existing = DB::table('catalog_pages')->where('parent_id', -1)->where('caption', 'API Clothing')->value('id');
        if ($existing) {
            DB::table('catalog_pages')->where('id', $existing)->update(['visible' => '0']);
            return (int) $existing;
        }
        return $this->createCataloguePage(-1, 'API Clothing', 6, false);
    }

    private function ensureApiClothingChildPage(int $rootPage, string $caption): int
    {
        $existing = DB::table('catalog_pages')->where('parent_id', $rootPage)->where('caption', $caption)->value('id');
        if ($existing) {
            DB::table('catalog_pages')->where('id', $existing)->update(['visible' => '0']);
            return (int) $existing;
        }
        return $this->createCataloguePage($rootPage, $caption, 6, false);
    }

    private function createCataloguePage(int $parentId, string $caption, int $minRank, bool $visible = true): int
    {
        DB::select("SELECT GET_LOCK('atom.habbofurni.page_allocator', 10) AS got_lock");
        try {
            $existing = DB::table('catalog_pages')->where('parent_id', $parentId)->where('caption', $caption)->value('id');
            if ($existing) return (int) $existing;

            $nextId = (int) DB::table('catalog_pages')->max('id') + 1;
            while (DB::table('catalog_pages')->where('id', $nextId)->exists()) $nextId++;
            $order = (int) DB::table('catalog_pages')->where('parent_id', $parentId)->max('order_num') + 1;

            DB::table('catalog_pages')->insert([
                'id' => $nextId,
                'parent_id' => $parentId,
                'caption_save' => mb_substr(Str::slug($caption, '_'), 0, 25),
                'caption' => mb_substr($caption, 0, 128),
                'page_layout' => 'default_3x3',
                'icon_color' => 0,
                'icon_image' => 1,
                'min_rank' => $minRank,
                'order_num' => $order,
                'visible' => $visible ? '1' : '0',
                'enabled' => '1',
                'club_only' => '0',
                'vip_only' => '0',
                'page_headline' => '',
                'page_teaser' => '',
                'page_special' => '',
                'page_text1' => '',
                'page_text2' => '',
                'page_text_details' => '',
                'page_text_teaser' => '',
                'room_id' => 0,
                'includes' => '',
                'catalog_mode' => 'NORMAL',
            ]);
            return $nextId;
        } finally {
            DB::select("SELECT RELEASE_LOCK('atom.habbofurni.page_allocator')");
        }
    }

    private function isClothingFurniture(array $row): bool
    {
        $hotelData = is_array($row['hotelData'] ?? null) ? $row['hotelData'] : [];

        foreach ([
            $row['classname'] ?? '',
            $row['name'] ?? '',
            $row['type'] ?? '',
            $row['category'] ?? '',
            $hotelData['classname'] ?? '',
            $hotelData['name'] ?? '',
            $hotelData['description'] ?? '',
            $hotelData['type'] ?? '',
            $hotelData['category'] ?? '',
            $hotelData['furni_line'] ?? '',
        ] as $value) {
            if ($this->isClothingLabel((string) $value)) return true;
        }

        return false;
    }

    private function isClothingLabel(string $value): bool
    {
        $value = strtolower(trim($value));
        if ($value === '') return false;

        $normal = preg_replace('/[^a-z0-9]+/', ' ', $value) ?: $value;
        $compact = preg_replace('/[^a-z0-9]+/', '', $value) ?: $value;
        $terms = [
            'clothing','clothes','wearable','wearables','garment','garments','wardrobe','avatarwear','fashion',
            'shirt','tshirt','hoodie','jacket','jumper','sweater','trousers','pants','shorts','skirt','dress',
            'shoes','boots','trainers','hat','cap','helmet','mask','hair','hairstyle','beard','glasses','eyewear'
        ];

        foreach ($terms as $term) {
            if (preg_match('/(^|\s)' . preg_quote($term, '/') . '(\s|$)/', $normal)) return true;
            if (str_starts_with($compact, $term) || str_ends_with($compact, $term)) return true;
        }

        return false;
    }

    private function folderName(array $hotelData): string
    {
        $raw = trim((string) ($hotelData['furni_line'] ?? ''));
        if ($raw === '') $raw = trim((string) ($hotelData['category'] ?? ''));
        if ($raw === '') $raw = 'Other';
        $raw = str_replace(['_', '-'], ' ', $raw);
        $raw = preg_replace('/\s+/', ' ', $raw);
        $caption = Str::title(trim((string) $raw));
        return mb_substr($caption ?: 'Other', 0, 128);
    }

    private function remoteBool(array $data, array $keys, bool $default): bool
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) continue;
            $v = $data[$key];
            if (is_bool($v)) return $v;
            if (is_numeric($v)) return (int) $v !== 0;
            if (is_string($v)) return in_array(strtolower($v), ['1', 'true', 'yes', 'on'], true);
        }
        return $default;
    }

    private function smartDisplayName(string $className): string
    {
        $value = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $className);
        $parts = preg_split('/[_\-\s]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) ?: [$className];
        if (count($parts) >= 3 && preg_match('/^(?:c|r|h|ltd|sc|bc|hc|v|s)\d{1,4}$/i', $parts[1])) {
            array_shift($parts);
            array_shift($parts);
        }
        return Str::title(implode(' ', $parts));
    }
}
