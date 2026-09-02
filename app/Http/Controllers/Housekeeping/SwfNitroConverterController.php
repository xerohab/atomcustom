<?php

namespace App\Http\Controllers\Housekeeping;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SwfNitroConverterController extends Controller
{
    private const PERMISSION = 'manage_furniture_uploader';
    private const MAX_FILES = 100;
    private const MAX_FILE_KB = 16384;

    private const CONVERTER_MAIN = '/var/www/nitro-converter/dist/Main.js';
    private const CONVERTER_CONFIG = '/var/www/nitro-converter/configuration.json';

    private function guard(): void
    {
        $perm = DB::table('housekeeping_permissions')
            ->where('permission', self::PERMISSION)
            ->first();

        $minRank = $perm ? (int) $perm->min_rank : 6;

        abort_unless(
            auth()->check() && (int) auth()->user()->rank >= $minRank,
            403
        );
    }

    private function root(): string
    {
        return storage_path('app/swf-nitro-converter');
    }

    private function cleanOldJobs(): void
    {
        $root = $this->root();

        if (!is_dir($root)) {
            return;
        }

        $cutoff = time() - (6 * 60 * 60);

        foreach (glob($root . '/*') ?: [] as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $mtime = @filemtime($path);

            if ($mtime !== false && $mtime < $cutoff) {
                $this->removeTree($path);
            }
        }
    }

    private function removeTree(string $path): void
    {
        if (!is_dir($path)) {
            if (is_file($path)) {
                @unlink($path);
            }

            return;
        }

        foreach (scandir($path) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $child = $path . '/' . $entry;

            if (is_dir($child) && !is_link($child)) {
                $this->removeTree($child);
            } else {
                @unlink($child);
            }
        }

        @rmdir($path);
    }

    private function validJob(string $job): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $job
        );
    }

    private function jobPath(string $job): string
    {
        abort_unless($this->validJob($job), 404);

        return $this->root() . '/' . $job;
    }

    private function outputDirectory(string $job): string
    {
        return $this->jobPath($job) . '/assets/bundled/furniture';
    }

    private function resultFiles(string $job): array
    {
        $dir = $this->outputDirectory($job);

        if (!is_dir($dir)) {
            return [];
        }

        $files = [];

        foreach (glob($dir . '/*.nitro') ?: [] as $file) {
            if (!is_file($file) || filesize($file) <= 0) {
                continue;
            }

            $files[] = [
                'name' => basename($file),
                'size' => filesize($file),
            ];
        }

        usort(
            $files,
            fn (array $a, array $b) => strnatcasecmp($a['name'], $b['name'])
        );

        return $files;
    }

    public function index(Request $request)
    {
        $this->guard();
        $this->cleanOldJobs();

        $job = trim((string) $request->query('job', ''));
        $files = [];
        $log = '';

        if ($job !== '' && $this->validJob($job)) {
            $jobPath = $this->jobPath($job);
            $files = $this->resultFiles($job);

            $logPath = $jobPath . '/conversion.log';

            if (is_file($logPath)) {
                $log = (string) file_get_contents($logPath);
            }
        } else {
            $job = '';
        }

        return view(
            'housekeeping.swf-nitro-converter',
            compact('job', 'files', 'log')
        );
    }

    public function convert(Request $request)
    {
        $this->guard();
        $this->cleanOldJobs();

        $request->validate([
            'swfs' => 'required|array|min:1|max:' . self::MAX_FILES,
            'swfs.*' => 'required|file|max:' . self::MAX_FILE_KB,
        ]);

        if (!is_file(self::CONVERTER_MAIN)) {
            abort(503, 'Nitro converter is not installed.');
        }

        $uploads = $request->file('swfs', []);

        if (count($uploads) > self::MAX_FILES) {
            throw ValidationException::withMessages([
                'swfs' => 'Too many SWF files in one conversion batch.',
            ]);
        }

        /*
         * Validate every file before creating the conversion job.
         */
        foreach ($uploads as $index => $file) {
            $fh = @fopen($file->getRealPath(), 'rb');

            if (!$fh) {
                throw ValidationException::withMessages([
                    'swfs.' . $index => 'Unable to read ' . $file->getClientOriginalName(),
                ]);
            }

            $magic = fread($fh, 3);
            fclose($fh);

            if (!in_array($magic, ['FWS', 'CWS', 'ZWS'], true)) {
                throw ValidationException::withMessages([
                    'swfs.' . $index =>
                        $file->getClientOriginalName() . ' is not a recognised SWF file.',
                ]);
            }
        }

        $job = (string) Str::uuid();
        $jobPath = $this->root() . '/' . $job;

        $input = $jobPath . '/assets/swf/furniture';
        $output = $jobPath . '/assets/bundled/furniture';

        foreach ([
            $input,
            $output,
            $jobPath . '/assets/swf/figure',
            $jobPath . '/assets/swf/effect',
            $jobPath . '/assets/swf/pet',
            $jobPath . '/assets/bundled/figure',
            $jobPath . '/assets/bundled/effect',
            $jobPath . '/assets/bundled/pet',
        ] as $dir) {
            if (!is_dir($dir) && !mkdir($dir, 0770, true) && !is_dir($dir)) {
                abort(500, 'Unable to create conversion workspace.');
            }
        }

        /*
         * Converter configuration is copied into the isolated working
         * directory because Nitro Converter resolves its assets relative to
         * the current working directory.
         */
        if (is_file(self::CONVERTER_CONFIG)) {
            copy(self::CONVERTER_CONFIG, $jobPath . '/configuration.json');
        } elseif (is_file('/var/www/nitro-converter/configuration.json.example')) {
            copy(
                '/var/www/nitro-converter/configuration.json.example',
                $jobPath . '/configuration.json'
            );
        }

        $usedNames = [];

        foreach ($uploads as $file) {
            $original = pathinfo(
                $file->getClientOriginalName(),
                PATHINFO_FILENAME
            );

            $base = preg_replace(
                '/[^A-Za-z0-9_-]+/',
                '_',
                trim((string) $original)
            );

            $base = trim((string) $base, '_-');

            if ($base === '') {
                $base = 'furniture';
            }

            $candidate = $base;
            $number = 2;

            while (isset($usedNames[strtolower($candidate)])) {
                $candidate = $base . '_' . $number++;
            }

            $usedNames[strtolower($candidate)] = true;

            $file->move($input, $candidate . '.swf');
        }

        /*
         * Run the already-compiled Nitro Converter directly.
         *
         * Each request has its own cwd/assets tree so separate housekeeping
         * conversions cannot overwrite one another.
         */
        $command = [
            '/usr/bin/env',
            'node',
            self::CONVERTER_MAIN,
            '--convert-swf',
        ];

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $pipes = [];

        $process = proc_open(
            $command,
            $descriptors,
            $pipes,
            $jobPath
        );

        if (!is_resource($process)) {
            $this->removeTree($jobPath);

            abort(500, 'Unable to start Nitro Converter.');
        }

        fclose($pipes[0]);

        stream_set_blocking($pipes[1], true);
        stream_set_blocking($pipes[2], true);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        $log =
            "Exit code: {$exitCode}\n\n" .
            "STDOUT:\n{$stdout}\n\n" .
            "STDERR:\n{$stderr}\n";

        file_put_contents($jobPath . '/conversion.log', $log);

        $results = $this->resultFiles($job);

        if ($exitCode !== 0 || !$results) {
            throw ValidationException::withMessages([
                'swfs' =>
                    'Conversion did not produce a Nitro bundle. ' .
                    'Open the conversion log shown below for the converter output.',
            ]);
        }

        return redirect()
            ->route('housekeeping.swf-nitro-converter', ['job' => $job])
            ->with(
                'success',
                count($results) .
                ' Nitro bundle' .
                (count($results) === 1 ? '' : 's') .
                ' generated successfully.'
            );
    }

    public function download(string $job, string $file): BinaryFileResponse
    {
        $this->guard();

        $dir = $this->outputDirectory($job);
        $name = basename($file);

        abort_unless(
            preg_match('/^[A-Za-z0-9_.-]+\.nitro$/i', $name),
            404
        );

        $path = $dir . '/' . $name;

        abort_unless(is_file($path) && filesize($path) > 0, 404);

        return response()->download(
            $path,
            $name,
            ['Content-Type' => 'application/octet-stream']
        );
    }

    public function downloadAll(string $job)
    {
        $this->guard();

        $files = $this->resultFiles($job);

        abort_unless(count($files) > 0, 404);

        if (count($files) === 1) {
            return $this->download($job, $files[0]['name']);
        }

        abort_unless(
            class_exists(\ZipArchive::class),
            503,
            'PHP ZipArchive is not installed. Individual Nitro downloads are still available.'
        );

        $jobPath = $this->jobPath($job);
        $zipPath = $jobPath . '/nitro-batch-' . $job . '.zip';

        $zip = new \ZipArchive();

        if ($zip->open(
            $zipPath,
            \ZipArchive::CREATE | \ZipArchive::OVERWRITE
        ) !== true) {
            abort(500, 'Unable to create Nitro ZIP.');
        }

        $output = $this->outputDirectory($job);

        foreach ($files as $file) {
            $zip->addFile(
                $output . '/' . $file['name'],
                $file['name']
            );
        }

        $zip->close();

        return response()->download(
            $zipPath,
            'nitro-converted-' . $job . '.zip',
            ['Content-Type' => 'application/zip']
        );
    }
}
