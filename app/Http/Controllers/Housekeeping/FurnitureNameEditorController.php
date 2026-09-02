<?php

namespace App\Http\Controllers\Housekeeping;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class FurnitureNameEditorController extends Controller
{
    private const PERMISSION = 'manage_furniture_uploader';
    private const DEFAULT_FURNIDATA = '/var/www/gamedata/config/FurnitureData.json';
    private const DEFAULT_EDITOR_MEMORY_LIMIT = '512M';

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

    public function index(Request $request)
    {
        $this->guard();

        $pages = DB::table('catalog_pages')
            ->select('id', 'parent_id', 'caption', 'min_rank', 'visible', 'enabled')
            ->where('enabled', '1')
            ->orderBy('caption')
            ->orderBy('id')
            ->get();

        $selectedPage = null;
        $items = collect();

        if ($request->filled('page')) {
            $pageId = (int) $request->input('page');

            $selectedPage = DB::table('catalog_pages')
                ->select('id', 'parent_id', 'caption', 'min_rank', 'visible', 'enabled')
                ->where('id', $pageId)
                ->first();

            if ($selectedPage) {
                $catalogRows = DB::table('catalog_items')
                    ->where('page_id', $pageId)
                    ->orderBy('order_number')
                    ->orderBy('id')
                    ->get();

                $itemIds = [];
                foreach ($catalogRows as $catalogRow) {
                    foreach ($this->splitItemIds($catalogRow->item_ids) as $itemId) {
                        $itemIds[$itemId] = $itemId;
                    }
                }

                $baseItems = empty($itemIds)
                    ? collect()
                    : DB::table('items_base')
                        ->whereIn('id', array_values($itemIds))
                        ->get()
                        ->keyBy('id');

                foreach ($catalogRows as $catalogRow) {
                    foreach ($this->splitItemIds($catalogRow->item_ids) as $itemId) {
                        $base = $baseItems->get($itemId);

                        $items->push((object) [
                            'catalog_item_id' => (int) $catalogRow->id,
                            'catalog_name' => (string) $catalogRow->catalog_name,
                            'order_number' => (int) $catalogRow->order_number,
                            'item_id' => (int) $itemId,
                            'resolved' => (bool) $base,
                            'sprite_id' => $base ? (int) $base->sprite_id : null,
                            'item_name' => $base ? (string) $base->item_name : null,
                            'public_name' => $base ? (string) $base->public_name : null,
                            'type' => $base ? (string) $base->type : null,
                        ]);
                    }
                }
            }
        }

        $iconBase = rtrim(
            (string) env('FURNI_ICON_PUBLIC_PATH', '/gamedata/icons'),
            '/'
        );

        return view(
            'housekeeping.furniture-name-editor',
            compact('pages', 'selectedPage', 'items', 'iconBase')
        );
    }

    public function update(Request $request, int $item)
    {
        $this->guard();
        $this->raiseEditorMemoryLimit();

        $validated = $request->validate([
            'public_name' => 'required|string|max:56',
            'catalog_item_id' => 'required|integer',
            'page_id' => 'required|integer',
        ]);

        $newName = trim((string) $validated['public_name']);
        if ($newName === '') {
            return back()->withErrors([
                'public_name' => 'The display name cannot be empty.',
            ]);
        }

        $catalogItemId = (int) $validated['catalog_item_id'];
        $pageId = (int) $validated['page_id'];

        $catalogRow = DB::table('catalog_items')
            ->where('id', $catalogItemId)
            ->where('page_id', $pageId)
            ->first();

        if (!$catalogRow) {
            return redirect()
                ->route('housekeeping.furniture-name-editor', ['page' => $pageId])
                ->with('error', 'That catalogue row no longer exists on this page.');
        }

        if (!in_array($item, $this->splitItemIds($catalogRow->item_ids), true)) {
            return redirect()
                ->route('housekeeping.furniture-name-editor', ['page' => $pageId])
                ->with('error', 'That furniture item is no longer part of the selected catalogue row.');
        }

        $base = DB::table('items_base')->where('id', $item)->first();
        if (!$base) {
            return redirect()
                ->route('housekeeping.furniture-name-editor', ['page' => $pageId])
                ->with('error', 'The furniture definition could not be found in items_base.');
        }

        $oldDbName = (string) $base->public_name;
        $className = (string) $base->item_name;

        $jsonChange = null;

        try {
            $jsonChange = $this->replaceFurnitureDataName(
                $item,
                $className,
                $newName
            );

            DB::transaction(function () use (
                $item,
                $className,
                $oldDbName,
                $newName,
                $catalogItemId,
                $pageId,
                $jsonChange
            ) {
                $current = DB::table('items_base')
                    ->where('id', $item)
                    ->where('item_name', $className)
                    ->first();

                if (!$current) {
                    throw new RuntimeException(
                        'The items_base row changed while the rename was being saved.'
                    );
                }

                if ((string) $current->public_name !== $oldDbName) {
                    throw new RuntimeException(
                        'The display name changed in the database while the rename was being saved. Reload the page and try again.'
                    );
                }

                DB::table('items_base')
                    ->where('id', $item)
                    ->where('item_name', $className)
                    ->update(['public_name' => $newName]);

                if (Schema::hasTable('furniture_name_edits')) {
                    DB::table('furniture_name_edits')->insert([
                        'user_id' => auth()->id(),
                        'username' => (string) auth()->user()->username,
                        'item_id' => $item,
                        'class_name' => $className,
                        'old_name' => $oldDbName,
                        'old_furnidata_name' => $jsonChange['old_name'],
                        'new_name' => $newName,
                        'catalog_item_id' => $catalogItemId,
                        'page_id' => $pageId,
                        'created_at' => now(),
                    ]);
                }
            });
        } catch (Throwable $e) {
            if ($jsonChange) {
                try {
                    $this->restoreFurnitureData(
                        $jsonChange['backup_path'],
                        $jsonChange['written_hash']
                    );
                } catch (Throwable $restoreError) {
                    report($restoreError);
                }
            }

            report($e);

            return redirect()
                ->route('housekeeping.furniture-name-editor', ['page' => $pageId])
                ->with('error', 'Rename failed: ' . $e->getMessage());
        }

        return redirect()
            ->route('housekeeping.furniture-name-editor', ['page' => $pageId])
            ->with(
                'success',
                sprintf(
                    'Renamed furniture #%d (%s) from "%s" to "%s".',
                    $item,
                    $className,
                    $oldDbName,
                    $newName
                )
            );
    }

    private function splitItemIds($raw): array
    {
        $parts = preg_split('/[;,]/', (string) $raw) ?: [];
        $ids = [];

        foreach ($parts as $part) {
            $part = trim($part);

            if ($part === '' || !ctype_digit($part)) {
                continue;
            }

            $id = (int) $part;
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    private function furnidataPath(): string
    {
        return (string) env('FURNIDATA_PATH', self::DEFAULT_FURNIDATA);
    }

    private function replaceFurnitureDataName(
        int $itemId,
        string $className,
        string $newName
    ): array {
        $this->raiseEditorMemoryLimit();

        $path = $this->furnidataPath();
        $directory = dirname($path);
        $lockPath = $path . '.name-editor.lock';

        if (!is_file($path)) {
            throw new RuntimeException("FurnitureData.json was not found at {$path}");
        }

        if (!is_readable($path)) {
            throw new RuntimeException("FurnitureData.json is not readable by PHP.");
        }

        if (!is_writable($directory)) {
            throw new RuntimeException(
                "The FurnitureData directory is not writable by PHP. Atomic replacement requires write permission on {$directory}"
            );
        }

        $lock = @fopen($lockPath, 'c');
        if (!$lock) {
            throw new RuntimeException(
                "Could not open the FurnitureData editor lock file: {$lockPath}"
            );
        }

        try {
            if (!flock($lock, LOCK_EX)) {
                throw new RuntimeException('Could not lock the FurnitureData name editor.');
            }

            for ($attempt = 1; $attempt <= 3; $attempt++) {
                clearstatcache(true, $path);

                $originalHash = hash_file('sha256', $path);
                if ($originalHash === false) {
                    throw new RuntimeException('Could not hash FurnitureData.json.');
                }

                $originalRaw = file_get_contents($path);
                if ($originalRaw === false) {
                    throw new RuntimeException('Could not read FurnitureData.json.');
                }

                try {
                    $data = json_decode($originalRaw, true, 512, JSON_THROW_ON_ERROR);
                } catch (Throwable $e) {
                    throw new RuntimeException(
                        'FurnitureData.json is not valid JSON: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }

                // The decoded PHP array uses far more RAM than the raw JSON.
                // Release the raw copy as soon as decoding is complete.
                unset($originalRaw);

                $location = $this->findFurnitureDataEntry(
                    $data,
                    $itemId,
                    $className
                );

                $section = $location['section'];
                $index = $location['index'];
                $oldName = (string) (
                    $data[$section]['furnitype'][$index]['name'] ?? ''
                );

                $data[$section]['furnitype'][$index]['name'] = $newName;

                try {
                    $updatedRaw = json_encode(
                        $data,
                        JSON_UNESCAPED_UNICODE |
                        JSON_UNESCAPED_SLASHES |
                        JSON_THROW_ON_ERROR
                    );
                } catch (Throwable $e) {
                    throw new RuntimeException(
                        'Could not encode the updated FurnitureData.json: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }

                // Free the large decoded structure before doing filesystem work.
                unset($data);

                $currentHash = hash_file('sha256', $path);
                if ($currentHash === false) {
                    throw new RuntimeException(
                        'Could not re-check FurnitureData.json before saving.'
                    );
                }

                if (!hash_equals($originalHash, $currentHash)) {
                    unset($updatedRaw);

                    if ($attempt < 3) {
                        usleep(150000);
                        continue;
                    }

                    throw new RuntimeException(
                        'FurnitureData.json changed while this rename was being prepared. Try again when the uploader is idle.'
                    );
                }

                $backup = $path . '.before-name-editor';
                if (!@copy($path, $backup)) {
                    throw new RuntimeException(
                        "Could not create the FurnitureData backup at {$backup}"
                    );
                }
                @chmod($backup, 0644);

                $this->atomicReplace($path, $updatedRaw);

                $writtenHash = hash('sha256', $updatedRaw);
                unset($updatedRaw);

                return [
                    'old_name' => $oldName,
                    'backup_path' => $backup,
                    'written_hash' => $writtenHash,
                ];
            }

            throw new RuntimeException('FurnitureData.json could not be updated safely.');
        } finally {
            @flock($lock, LOCK_UN);
            @fclose($lock);
        }
    }

    private function findFurnitureDataEntry(
        array $data,
        int $itemId,
        string $className
    ): array {
        $idMatch = null;
        $classMatch = null;

        foreach (['roomitemtypes', 'wallitemtypes'] as $section) {
            $entries = $data[$section]['furnitype'] ?? null;
            if (!is_array($entries)) {
                continue;
            }

            foreach ($entries as $index => $entry) {
                if (!is_array($entry)) {
                    continue;
                }

                $entryId = isset($entry['id']) ? (int) $entry['id'] : null;
                $entryClass = isset($entry['classname'])
                    ? (string) $entry['classname']
                    : '';

                if ($entryId === $itemId && $entryClass === $className) {
                    return ['section' => $section, 'index' => $index];
                }

                if ($entryId === $itemId) {
                    $idMatch = [
                        'section' => $section,
                        'index' => $index,
                        'classname' => $entryClass,
                    ];
                }

                if ($entryClass === $className) {
                    $classMatch = [
                        'section' => $section,
                        'index' => $index,
                        'id' => $entryId,
                    ];
                }
            }
        }

        if ($idMatch) {
            throw new RuntimeException(
                sprintf(
                    'FurnitureData ID %d exists, but its classname is "%s" instead of "%s". Nothing was changed.',
                    $itemId,
                    $idMatch['classname'],
                    $className
                )
            );
        }

        if ($classMatch) {
            throw new RuntimeException(
                sprintf(
                    'FurnitureData classname "%s" exists, but its ID is %s instead of %d. Nothing was changed.',
                    $className,
                    (string) $classMatch['id'],
                    $itemId
                )
            );
        }

        throw new RuntimeException(
            sprintf(
                'Could not find FurnitureData entry ID %d with classname "%s". Nothing was changed.',
                $itemId,
                $className
            )
        );
    }

    private function atomicReplace(string $path, string $raw): void
    {
        $directory = dirname($path);
        $temporary = tempnam($directory, '.furnidata-name-');

        if ($temporary === false) {
            throw new RuntimeException(
                'Could not create a temporary FurnitureData file.'
            );
        }

        try {
            $written = file_put_contents($temporary, $raw, LOCK_EX);
            if ($written === false || $written !== strlen($raw)) {
                throw new RuntimeException(
                    'Could not completely write the temporary FurnitureData file.'
                );
            }

            if (!@chmod($temporary, 0644)) {
                throw new RuntimeException(
                    'Could not set FurnitureData permissions on the temporary file.'
                );
            }

            if (!@rename($temporary, $path)) {
                throw new RuntimeException(
                    'Could not atomically replace FurnitureData.json.'
                );
            }
        } finally {
            if (is_file($temporary)) {
                @unlink($temporary);
            }
        }
    }

    private function restoreFurnitureData(
        string $backupPath,
        string $expectedWrittenHash
    ): void {
        $path = $this->furnidataPath();

        if (!is_file($path) || !is_file($backupPath)) {
            return;
        }

        $currentHash = hash_file('sha256', $path);
        if ($currentHash === false) {
            return;
        }

        // Never overwrite somebody else's newer FurnitureData change.
        if (!hash_equals($expectedWrittenHash, $currentHash)) {
            throw new RuntimeException(
                'The database rename failed, but FurnitureData changed again before rollback. The newer file was left untouched; restore manually from the backup if needed.'
            );
        }

        $backupRaw = file_get_contents($backupPath);
        if ($backupRaw === false) {
            throw new RuntimeException(
                'Could not read the FurnitureData backup during rollback.'
            );
        }

        $this->atomicReplace($path, $backupRaw);
        unset($backupRaw);
    }

    private function raiseEditorMemoryLimit(): void
    {
        $wanted = (string) env(
            'FURNITURE_EDITOR_MEMORY_LIMIT',
            self::DEFAULT_EDITOR_MEMORY_LIMIT
        );

        // This affects only the current PHP request. If the host enforces a
        // php_admin_value memory_limit, PHP may refuse to raise it; in that
        // case the FPM/php.ini limit must be changed server-side.
        @ini_set('memory_limit', $wanted);
    }
}
