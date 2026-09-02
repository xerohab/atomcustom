<?php

namespace App\Http\Controllers\Housekeeping;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class ClothingManagerController extends Controller
{
    private const PERMISSION = 'manage_furniture_uploader';

    private const DEFAULT_FIGUREDATA =
        '/var/www/gamedata/config/FigureData.json';

    private const DEFAULT_FIGUREMAP =
        '/var/www/gamedata/config/FigureMap.json';

    private const DEFAULT_FURNIDATA =
        '/var/www/gamedata/config/FurnitureData.json';

    private const PAGE_SIZE = 75;
    private const DEFAULT_MEMORY_LIMIT = '512M';

    private function raiseMemoryLimit(): void
    {
        $wanted = (string) env(
            'CLOTHING_MANAGER_MEMORY_LIMIT',
            self::DEFAULT_MEMORY_LIMIT
        );

        @ini_set('memory_limit', $wanted);
    }

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

    public function index(Request $request)
    {
        $this->raiseMemoryLimit();
        $this->guard();

        $library = $this->buildLibrary();

        $q = strtolower(trim((string) $request->input('q', '')));
        $type = trim((string) $request->input('type', ''));
        $gender = strtoupper(trim((string) $request->input('gender', '')));
        $state = trim((string) $request->input('state', ''));
        $mapping = trim((string) $request->input('mapping', ''));

        $rows = collect($library['sets']);

        if ($q !== '') {
            $rows = $rows->filter(function ($row) use ($q) {
                $haystack = strtolower(
                    implode(' ', [
                        $row['set_id'],
                        $row['label'],
                        $row['type'],
                        $row['gender'],
                        implode(' ', $row['wrapper_names']),
                        implode(' ', $row['wrapper_classes']),
                        implode(' ', $row['library_ids']),
                    ])
                );

                return str_contains($haystack, $q);
            });
        }

        if ($type !== '') {
            $rows = $rows->where('type', $type);
        }

        if (in_array($gender, ['M', 'F', 'U'], true)) {
            $rows = $rows->where('gender', $gender);
        }

        if ($state === 'free') {
            $rows = $rows->where('sellable', false);
        } elseif ($state === 'purchasable') {
            $rows = $rows->where('sellable', true);
        }

        if ($mapping === 'mapped') {
            $rows = $rows->filter(
                fn ($row) => count($row['wrapper_classes']) > 0
            );
        } elseif ($mapping === 'ready') {
            $rows = $rows->where('can_purchase', true);
        } elseif ($mapping === 'unmapped') {
            $rows = $rows->filter(
                fn ($row) => count($row['wrapper_classes']) === 0
            );
        } elseif ($mapping === 'broken') {
            $rows = $rows->filter(
                fn ($row) => count($row['wrapper_classes']) > 0
                    && !$row['can_purchase']
            );
        }

        $rows = $rows
            ->sortBy([
                ['type', 'asc'],
                ['label', 'asc'],
                ['set_id', 'asc'],
            ])
            ->values();

        $page = max(1, (int) $request->input('page', 1));

        $paginator = new LengthAwarePaginator(
            $rows->slice(($page - 1) * self::PAGE_SIZE, self::PAGE_SIZE)->values(),
            $rows->count(),
            self::PAGE_SIZE,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

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

        return view('housekeeping.clothing-manager', [
            'items' => $paginator,
            'pages' => $pages,
            'types' => $library['types'],
            'stats' => $library['stats'],
            'figureDataWritable' =>
                is_writable(dirname($this->figureDataPath())),
        ]);
    }

    public function apply(Request $request)
    {
        $this->raiseMemoryLimit();
        $this->guard();

        $validated = $request->validate([
            'selected' => 'required|array|min:1|max:500',
            'selected.*' => 'required|integer|min:1',

            'mode' => 'required|in:free,purchasable',

            'page_id' => 'nullable|integer|min:1',
            'credits' => 'nullable|integer|min:0|max:2147483647',
            'diamonds' => 'nullable|integer|min:0|max:2147483647',
        ]);

        $selected = array_values(array_unique(array_map(
            'intval',
            $validated['selected']
        )));

        $mode = $validated['mode'];

        $pageId = isset($validated['page_id'])
            ? (int) $validated['page_id']
            : null;

        $credits = isset($validated['credits'])
            ? (int) $validated['credits']
            : 0;

        $diamonds = isset($validated['diamonds'])
            ? (int) $validated['diamonds']
            : 0;

        if ($mode === 'purchasable') {
            if (!$pageId) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Choose a catalogue page before making clothing purchasable.'
                    );
            }

            $pageExists = DB::table('catalog_pages')
                ->where('id', $pageId)
                ->where('enabled', '1')
                ->exists();

            if (!$pageExists) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'The selected catalogue page does not exist or is disabled.'
                    );
            }
        }

        try {
            if ($mode === 'free') {
                $changed = $this->makeFree($selected);

                return back()->with(
                    'success',
                    sprintf(
                        '%d figure set(s) were set to Free.',
                        $changed
                    )
                );
            }

            $result = $this->makePurchasable(
                $selected,
                $pageId,
                $credits,
                $diamonds
            );

            return back()->with(
                'success',
                sprintf(
                    '%d clothing product(s) made purchasable. %d FigureData set(s) updated. %d catalogue item(s) created and %d updated.',
                    $result['products'],
                    $result['sets'],
                    $result['catalog_created'],
                    $result['catalog_updated']
                )
            );
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Clothing update failed: ' . $e->getMessage());
        }
    }

    private function makeFree(array $selected): int
    {
        return $this->updateFigureDataSellable(
            $selected,
            false
        );
    }

    private function makePurchasable(
        array $selected,
        int $pageId,
        int $credits,
        int $diamonds
    ): array {
        $library = $this->buildLibrary();

        $bySet = [];

        foreach ($library['sets'] as $row) {
            $bySet[(int) $row['set_id']] = $row;
        }

        $products = [];

        foreach ($selected as $setId) {
            if (!isset($bySet[$setId])) {
                throw new RuntimeException(
                    "Figure set {$setId} no longer exists."
                );
            }

            $row = $bySet[$setId];

            if (!$row['can_purchase']) {
                throw new RuntimeException(
                    "Figure set {$setId} ({$row['label']}) cannot safely be made purchasable yet. It has no complete purchasable furniture wrapper."
                );
            }

            $product = $row['preferred_wrapper'];

            if (!$product) {
                throw new RuntimeException(
                    "No purchasable wrapper is available for figure set {$setId}."
                );
            }

            $products[$product['classname']] = $product;
        }

        $allSetIds = [];

        foreach ($products as $product) {
            foreach ($product['set_ids'] as $setId) {
                $allSetIds[(int) $setId] = true;
            }
        }

        $allSetIds = array_keys($allSetIds);

        /*
         * catalog_clothing/users_clothing use MyISAM on this database,
         * so this deliberately does not pretend the whole operation is a
         * transactional DB+JSON unit.
         *
         * FigureData gets a backup before its atomic replacement.
         */

        $catalogCreated = 0;
        $catalogUpdated = 0;

        foreach ($products as $className => $product) {
            $base = DB::table('items_base')
                ->where('item_name', $className)
                ->first();

            if (!$base) {
                throw new RuntimeException(
                    "items_base is missing wrapper {$className}."
                );
            }

            $setString = implode(
                ',',
                array_map('intval', $product['set_ids'])
            );

            $clothingRow = DB::table('catalog_clothing')
                ->where('name', $className)
                ->first();

            if ($clothingRow) {
                DB::table('catalog_clothing')
                    ->where('id', $clothingRow->id)
                    ->update([
                        'setid' => $setString,
                    ]);
            } else {
                DB::table('catalog_clothing')->insert([
                    'name' => $className,
                    'setid' => $setString,
                ]);
            }

            $catalogItem = $this->findCatalogItemForBase(
                (int) $base->id
            );

            if ($catalogItem) {
                DB::table('catalog_items')
                    ->where('id', $catalogItem->id)
                    ->update([
                        'page_id' => $pageId,
                        'cost_credits' => $credits,
                        'cost_points' => $diamonds,
                        'points_type' => $diamonds > 0 ? 5 : 0,
                    ]);

                $catalogUpdated++;
            } else {
                $maxOrder = (int) DB::table('catalog_items')
                    ->where('page_id', $pageId)
                    ->max('order_number');

                DB::table('catalog_items')->insert([
                    'item_ids' => (string) $base->id,
                    'page_id' => $pageId,
                    'offer_id' => isset($base->sprite_id)
                        ? (int) $base->sprite_id
                        : -1,
                    'catalog_name' => (string) $base->item_name,
                    'song_id' => 0,
                    'order_number' => $maxOrder + 1,
                    'cost_credits' => $credits,
                    'cost_points' => $diamonds,
                    'points_type' => $diamonds > 0 ? 5 : 0,
                    'amount' => 1,
                    'limited_sells' => 0,
                    'limited_stack' => 0,
                    'extradata' => '',
                    'badge' => null,
                    'have_offer' => '1',
                    'club_only' => '0',
                    'rate' => null,
                ]);

                $catalogCreated++;
            }
        }

        $changedSets = $this->updateFigureDataSellable(
            $allSetIds,
            true
        );

        return [
            'products' => count($products),
            'sets' => $changedSets,
            'catalog_created' => $catalogCreated,
            'catalog_updated' => $catalogUpdated,
        ];
    }

    private function findCatalogItemForBase(int $baseId)
    {
        return DB::table('catalog_items')
            ->whereRaw("item_ids REGEXP '^[0-9]+$'")
            ->whereRaw(
                'CAST(item_ids AS UNSIGNED) = ?',
                [$baseId]
            )
            ->orderBy('id')
            ->first();
    }

    private function updateFigureDataSellable(
        array $targetIds,
        bool $sellable
    ): int {
        $path = $this->figureDataPath();
        $directory = dirname($path);
        $lockPath = $path . '.clothing-manager.lock';

        if (!is_file($path)) {
            throw new RuntimeException(
                "FigureData.json was not found at {$path}."
            );
        }

        if (!is_readable($path)) {
            throw new RuntimeException(
                'FigureData.json is not readable by PHP.'
            );
        }

        if (!is_writable($directory)) {
            throw new RuntimeException(
                "The FigureData directory is not writable by PHP: {$directory}"
            );
        }

        $wanted = [];

        foreach ($targetIds as $id) {
            $wanted[(int) $id] = true;
        }

        $lock = @fopen($lockPath, 'c');

        if (!$lock) {
            throw new RuntimeException(
                "Could not open clothing manager lock {$lockPath}."
            );
        }

        try {
            if (!flock($lock, LOCK_EX)) {
                throw new RuntimeException(
                    'Could not acquire the FigureData clothing lock.'
                );
            }

            $originalHash = hash_file('sha256', $path);

            if ($originalHash === false) {
                throw new RuntimeException(
                    'Could not hash FigureData.json.'
                );
            }

            $raw = file_get_contents($path);

            if ($raw === false) {
                throw new RuntimeException(
                    'Could not read FigureData.json.'
                );
            }

            $data = json_decode(
                $raw,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            unset($raw);

            $found = [];
            $changed = 0;

            foreach ($data['setTypes'] ?? [] as &$setType) {
                foreach ($setType['sets'] ?? [] as &$set) {
                    $id = (int) ($set['id'] ?? 0);

                    if (!$id || !isset($wanted[$id])) {
                        continue;
                    }

                    $found[$id] = true;

                    $current = (bool) ($set['sellable'] ?? false);

                    if ($current !== $sellable) {
                        $set['sellable'] = $sellable;
                        $changed++;
                    }
                }

                unset($set);
            }

            unset($setType);

            $missing = array_diff(
                array_keys($wanted),
                array_keys($found)
            );

            if ($missing) {
                throw new RuntimeException(
                    'These FigureData set IDs disappeared before saving: ' .
                    implode(', ', $missing)
                );
            }

            if ($changed === 0) {
                return 0;
            }

            $currentHash = hash_file('sha256', $path);

            if (
                $currentHash === false ||
                !hash_equals($originalHash, $currentHash)
            ) {
                throw new RuntimeException(
                    'FigureData.json changed while this operation was being prepared. Nothing was written.'
                );
            }

            $backup =
                $path .
                '.before-clothing-manager-' .
                date('Ymd-His');

            if (!@copy($path, $backup)) {
                throw new RuntimeException(
                    "Could not create FigureData backup {$backup}."
                );
            }

            @chmod($backup, 0644);

            $updated = json_encode(
                $data,
                JSON_UNESCAPED_SLASHES |
                JSON_UNESCAPED_UNICODE |
                JSON_THROW_ON_ERROR
            );

            unset($data);

            $this->atomicReplace($path, $updated);

            return $changed;
        } finally {
            @flock($lock, LOCK_UN);
            @fclose($lock);
        }
    }

    private function atomicReplace(string $path, string $raw): void
    {
        $directory = dirname($path);

        $temporary = tempnam(
            $directory,
            '.figuredata-clothing-'
        );

        if ($temporary === false) {
            throw new RuntimeException(
                'Could not create temporary FigureData file.'
            );
        }

        try {
            $written = file_put_contents(
                $temporary,
                $raw,
                LOCK_EX
            );

            if (
                $written === false ||
                $written !== strlen($raw)
            ) {
                throw new RuntimeException(
                    'Could not completely write temporary FigureData file.'
                );
            }

            @chmod($temporary, 0644);

            if (!@rename($temporary, $path)) {
                throw new RuntimeException(
                    'Could not atomically replace FigureData.json.'
                );
            }
        } finally {
            if (is_file($temporary)) {
                @unlink($temporary);
            }
        }
    }

    private function buildLibrary(): array
    {
        $figure = $this->readJson($this->figureDataPath());
        $furni = $this->readJson($this->furnitureDataPath());
        $map = $this->readJson($this->figureMapPath());

        $sets = [];
        $existingIds = [];
        $types = [];

        /*
         * Build one safe/default colour ID per FigureData palette.
         * Prefer a selectable colour, otherwise use the first colour.
         */
        $paletteDefaults = [];

        foreach ($figure['palettes'] ?? [] as $palette) {
            $paletteId = (int) ($palette['id'] ?? 0);

            if ($paletteId <= 0) {
                continue;
            }

            $firstColour = null;
            $selectableColour = null;

            foreach ($palette['colors'] ?? [] as $colour) {
                $colourId = (int) ($colour['id'] ?? 0);

                if ($colourId <= 0) {
                    continue;
                }

                if ($firstColour === null) {
                    $firstColour = $colourId;
                }

                if (
                    $selectableColour === null &&
                    (bool) ($colour['selectable'] ?? false)
                ) {
                    $selectableColour = $colourId;
                }
            }

            $paletteDefaults[$paletteId] =
                $selectableColour ?? $firstColour;
        }

        foreach ($figure['setTypes'] ?? [] as $setType) {
            $type = (string) ($setType['type'] ?? '?');
            $paletteId = (int) ($setType['paletteId'] ?? 0);

            $types[$type] = true;

            foreach ($setType['sets'] ?? [] as $set) {
                $id = (int) ($set['id'] ?? 0);

                if (!$id) {
                    continue;
                }

                $existingIds[$id] = true;

                $sets[$id] = [
                    'set_id' => $id,
                    'type' => $type,
                    'gender' => strtoupper(
                        (string) ($set['gender'] ?? 'U')
                    ),
                    'selectable' =>
                        (bool) ($set['selectable'] ?? false),
                    'sellable' =>
                        (bool) ($set['sellable'] ?? false),
                    'parts' => $set['parts'] ?? [],
                    'preview_colors' =>
                        $this->buildPreviewColours(
                            $set,
                            $paletteDefaults[$paletteId] ?? null
                        ),
                    'library_ids' => [],
                    'label' => "Set {$id}",
                    'wrapper_names' => [],
                    'wrapper_classes' => [],
                    'wrappers' => [],
                    'preferred_wrapper' => null,
                    'preview_url' => null,
                    'preview_generate_url' => null,
                    'icon_preview_url' => null,

                    /*
                     * Rendering diagnostics.
                     *
                     * preview_blocked is only set when we can positively
                     * prove that a FigureMap library required by this
                     * FigureData set has no corresponding .nitro file.
                     */
                    'preview_blocked' => false,
                    'preview_problem' => null,
                    'missing_asset_libraries' => [],

                    'can_purchase' => false,
                    'catalog' => null,
                ];
            }
        }

        $partLibraries = [];
        $libraryAssetExists = [];

        foreach ($map['libraries'] ?? [] as $library) {
            $libraryId = (string) ($library['id'] ?? '');

            if ($libraryId === '') {
                continue;
            }

            /*
             * FigureMap library IDs correspond to the clothing asset
             * bundle name used by the avatar renderer.
             */
            $libraryAssetExists[$libraryId] = is_file(
                '/var/www/gamedata/clothes/' .
                $libraryId .
                '.nitro'
            );

            foreach ($library['parts'] ?? [] as $part) {
                $partId = (int) ($part['id'] ?? 0);
                $partType = (string) ($part['type'] ?? '');

                if (!$partId || $partType === '') {
                    continue;
                }

                $key = $partType . ':' . $partId;

                $partLibraries[$key][] = $libraryId;
            }
        }

        foreach ($sets as &$row) {
            $libraries = [];

            foreach ($row['parts'] as $part) {
                $key =
                    (string) ($part['type'] ?? '') .
                    ':' .
                    (int) ($part['id'] ?? 0);

                foreach ($partLibraries[$key] ?? [] as $lib) {
                    $libraries[$lib] = true;
                }
            }

            $row['library_ids'] = array_keys($libraries);

            /*
             * Only block a preview when FigureMap explicitly maps one
             * of this set's parts to a library and that library's Nitro
             * file is genuinely absent.
             *
             * An unmapped auxiliary part is NOT automatically treated
             * as broken here.
             */
            $missingAssetLibraries = [];

            foreach ($row['library_ids'] as $libraryId) {
                if (
                    !($libraryAssetExists[$libraryId] ?? false)
                ) {
                    $missingAssetLibraries[] = $libraryId;
                }
            }

            if ($missingAssetLibraries) {
                $row['preview_blocked'] = true;
                $row['preview_problem'] = 'Missing asset';
                $row['missing_asset_libraries'] =
                    $missingAssetLibraries;
            }

            if ($row['library_ids']) {
                $row['label'] = $this->humaniseLibraryId(
                    $row['library_ids'][0]
                );
            }
        }

        unset($row);

        $furniEntries = [];

        $collect = function ($node) use (&$collect, &$furniEntries) {
            if (!is_array($node)) {
                return;
            }

            if (
                isset($node['classname']) &&
                array_key_exists('specialtype', $node)
            ) {
                $furniEntries[] = $node;
            }

            foreach ($node as $value) {
                if (is_array($value)) {
                    $collect($value);
                }
            }
        };

        $collect($furni);

        $wrapperClasses = [];

        foreach ($furniEntries as $entry) {
            $className = trim(
                (string) ($entry['classname'] ?? '')
            );

            $specialType = (int) ($entry['specialtype'] ?? 0);

            if (
                $className === '' ||
                $specialType !== 23
            ) {
                continue;
            }

            $setIds = $this->parseSetIds(
                $entry['customparams'] ?? ''
            );

            if (!$setIds) {
                continue;
            }

            $missing = [];

            foreach ($setIds as $setId) {
                if (!isset($existingIds[$setId])) {
                    $missing[] = $setId;
                }
            }

            $wrapper = [
                'classname' => $className,
                'name' => trim(
                    (string) ($entry['name'] ?? $className)
                ),
                'furnidata_id' =>
                    (int) ($entry['id'] ?? 0),
                'set_ids' => $setIds,
                'missing_set_ids' => $missing,
                'complete' => count($missing) === 0,
            ];

            $wrapperClasses[$className] = true;

            foreach ($setIds as $setId) {
                if (!isset($sets[$setId])) {
                    continue;
                }

                $sets[$setId]['wrappers'][] = $wrapper;
                $sets[$setId]['wrapper_classes'][] =
                    $className;
                $sets[$setId]['wrapper_names'][] =
                    $wrapper['name'];
            }
        }

        $baseByClass = [];

        foreach (
            array_chunk(array_keys($wrapperClasses), 500)
            as $chunk
        ) {
            $rows = DB::table('items_base')
                ->select(
                    'id',
                    'sprite_id',
                    'item_name',
                    'public_name',
                    'interaction_type'
                )
                ->whereIn('item_name', $chunk)
                ->get();

            foreach ($rows as $base) {
                $baseByClass[(string) $base->item_name] =
                    $base;
            }
        }

        foreach ($sets as &$row) {
            $preferred = null;

            foreach ($row['wrappers'] as $wrapper) {
                if (!$wrapper['complete']) {
                    continue;
                }

                if (
                    !isset(
                        $baseByClass[$wrapper['classname']]
                    )
                ) {
                    continue;
                }

                if (
                    $preferred === null ||
                    (
                        str_starts_with(
                            strtolower($preferred['classname']),
                            'clothing_nt_'
                        ) &&
                        !str_starts_with(
                            strtolower($wrapper['classname']),
                            'clothing_nt_'
                        )
                    )
                ) {
                    $preferred = $wrapper;
                }
            }

            if ($preferred) {
                $base =
                    $baseByClass[$preferred['classname']];

                $preferred['base_id'] = (int) $base->id;
                $preferred['sprite_id'] =
                    (int) $base->sprite_id;

                $row['preferred_wrapper'] = $preferred;
                $row['can_purchase'] = true;

                $row['preview_url'] = $this->resolvePreviewUrl(
                    $preferred['classname'],
                    (int) $base->sprite_id
                );

                $row['icon_preview_url'] = $row['preview_url'];

                $catalog =
                    $this->findCatalogItemForBase(
                        (int) $base->id
                    );

                if ($catalog) {
                    $row['catalog'] = [
                        'id' => (int) $catalog->id,
                        'page_id' =>
                            (int) $catalog->page_id,
                        'credits' =>
                            (int) $catalog->cost_credits,
                        'diamonds' =>
                            (int) (
                                (int) $catalog->points_type === 5
                                    ? $catalog->cost_points
                                    : 0
                            ),
                    ];
                }

                if (
                    $preferred['name'] !== '' &&
                    !str_starts_with(
                        strtolower($preferred['name']),
                        'clothing_'
                    )
                ) {
                    $row['label'] =
                        $preferred['name'];
                }
            }

            $row['wrapper_classes'] =
                array_values(array_unique(
                    $row['wrapper_classes']
                ));

            $row['wrapper_names'] =
                array_values(array_unique(
                    $row['wrapper_names']
                ));
        }

        unset($row);

        /*
         * The clothing manager preview must not depend on a furniture
         * wrapper or catalogue mapping. Every FigureData set should
         * get an avatar preview where the configured avatar imager
         * can render it.
         */
        $baseLooks = $this->getPreviewBaseLooks();

        foreach ($sets as &$row) {
            /*
             * Do not send known-broken clothing assets to Pixinode.
             * A missing asset can otherwise stall or terminate the
             * serialized renderer and affect every preview after it.
             */
            if (($row['preview_blocked'] ?? false) === true) {
                continue;
            }

            $avatarPreview = $this->resolveAvatarPreviewUrl(
                $row,
                $baseLooks
            );

            if ($avatarPreview !== null) {
                $row['preview_generate_url'] =
                    $this->buildPreviewGenerationUrl(
                        $row,
                        $avatarPreview
                    );

                $row['preview_url'] =
                    $this->resolveCachedAvatarPreviewUrl(
                        $row,
                        $avatarPreview
                    );
            }
        }

        unset($row);

        $free = 0;
        $sellable = 0;
        $mapped = 0;
        $ready = 0;

        foreach ($sets as $row) {
            if ($row['sellable']) {
                $sellable++;
            } else {
                $free++;
            }

            if ($row['wrapper_classes']) {
                $mapped++;
            }

            if ($row['can_purchase']) {
                $ready++;
            }
        }

        return [
            'sets' => array_values($sets),
            'types' => array_keys($types),
            'stats' => [
                'total' => count($sets),
                'free' => $free,
                'sellable' => $sellable,
                'mapped' => $mapped,
                'ready' => $ready,
            ],
        ];
    }

    private function buildPreviewColours(
        array $set,
        ?int $defaultColour
    ): array {
        if (!$defaultColour) {
            return [];
        }

        $maxColourIndex = 0;

        foreach ($set['parts'] ?? [] as $part) {
            if (!(bool) ($part['colorable'] ?? false)) {
                continue;
            }

            $colourIndex = (int) ($part['colorindex'] ?? 0);

            if ($colourIndex > $maxColourIndex) {
                $maxColourIndex = $colourIndex;
            }
        }

        /*
         * Some sets are marked colourable at set level even when
         * individual part metadata is incomplete.
         */
        if (
            $maxColourIndex === 0 &&
            (bool) ($set['colorable'] ?? false)
        ) {
            $maxColourIndex = 1;
        }

        if ($maxColourIndex <= 0) {
            return [];
        }

        return array_fill(
            0,
            min($maxColourIndex, 4),
            $defaultColour
        );
    }

    /**
     * Serve or create a cached clothing preview.
     *
     * Pixinode is deliberately used only when the PNG does not already
     * exist. Future manager page loads are served from the static cache.
     */
    /**
     * Return preview-generation jobs without rendering anything.
     *
     * mode=pending : uncached valid previews
     * mode=failed  : previously failed previews
     *
     * set_ids may optionally restrict the manifest to the currently
     * visible Clothing Manager page.
     */
    public function previewManifest(
        \Illuminate\Http\Request $request
    ) {
        $this->raiseMemoryLimit();
        $this->guard();

        $mode = strtolower(
            trim((string) $request->query('mode', 'pending'))
        );

        if (!in_array($mode, ['pending', 'failed'], true)) {
            $mode = 'pending';
        }

        $restrictIds = [];

        $rawIds = trim(
            (string) $request->query('set_ids', '')
        );

        if ($rawIds !== '') {
            foreach (explode(',', $rawIds) as $value) {
                $id = (int) trim($value);

                if ($id > 0) {
                    $restrictIds[$id] = true;
                }
            }
        }

        $library = $this->buildLibrary();

        $jobs = [];

        $counts = [
            'cached' => 0,
            'pending' => 0,
            'failed' => 0,
            'missing_asset' => 0,
            'unavailable' => 0,
        ];

        foreach ($library['sets'] ?? [] as $row) {
            $setId = (int) ($row['set_id'] ?? 0);

            if ($setId <= 0) {
                continue;
            }

            if (
                $restrictIds &&
                !isset($restrictIds[$setId])
            ) {
                continue;
            }

            if (($row['preview_blocked'] ?? false) === true) {
                $counts['missing_asset']++;
                continue;
            }

            if (!empty($row['preview_url'])) {
                $counts['cached']++;
                continue;
            }

            $problem = (string) (
                $row['preview_problem'] ?? ''
            );

            if ($problem === 'Render failed') {
                $counts['failed']++;

                if (
                    $mode === 'failed' &&
                    !empty($row['preview_generate_url'])
                ) {
                    $jobs[] = [
                        'set_id' => $setId,
                        'label' => (string) (
                            $row['label'] ?? "Set {$setId}"
                        ),
                        'url' => (string)
                            $row['preview_generate_url'],
                    ];
                }

                continue;
            }

            if (empty($row['preview_generate_url'])) {
                $counts['unavailable']++;
                continue;
            }

            $counts['pending']++;

            if ($mode === 'pending') {
                $jobs[] = [
                    'set_id' => $setId,
                    'label' => (string) (
                        $row['label'] ?? "Set {$setId}"
                    ),
                    'url' => (string)
                        $row['preview_generate_url'],
                ];
            }
        }

        return response()->json([
            'ok' => true,
            'mode' => $mode,
            'jobs' => $jobs,
            'counts' => $counts,
            'job_count' => count($jobs),
        ]);
    }

    public function preview(
        \Illuminate\Http\Request $request,
        int $setId
    ) {
        $this->raiseMemoryLimit();
        $this->guard();

        abort_unless($setId > 0, 404);

        $gender = strtolower(
            trim((string) $request->query('gender', 'u'))
        );

        if (!in_array($gender, ['m', 'f'], true)) {
            $gender = 'u';
        }

        $figure = trim(
            (string) $request->query('figure', '')
        );

        abort_if(
            $figure === '' ||
            strlen($figure) > 2000 ||
            !preg_match('/^[A-Za-z0-9.\-]+$/', $figure),
            400,
            'Invalid clothing preview figure.'
        );

        $fileName = $this->previewCacheFileName(
            $setId,
            $gender
        );

        $directory = $this->previewCacheDirectory();
        $path = $directory . DIRECTORY_SEPARATOR . $fileName;

        $failedPath = $path . '.failed';

        /*
         * Explicit Retry Failed requests may clear the quarantine marker.
         * Normal manager browsing never calls this endpoint.
         */
        if (
            $request->boolean('retry') &&
            is_file($failedPath)
        ) {
            @unlink($failedPath);
        }

        /*
         * Fast path: this item was already generated.
         */
        if (is_file($path) && filesize($path) > 0) {
            return response()->file(
                $path,
                [
                    'Content-Type' => 'image/png',
                    'Cache-Control' =>
                        'public, max-age=31536000, immutable',
                ]
            );
        }

        if (!is_dir($directory)) {
            if (
                !@mkdir($directory, 0775, true) &&
                !is_dir($directory)
            ) {
                abort(
                    500,
                    'Could not create clothing preview cache.'
                );
            }
        }

        abort_unless(
            is_writable($directory),
            500,
            'Clothing preview cache is not writable.'
        );

        /*
         * One lock per preview prevents duplicate browser requests from
         * asking Pixinode to render the same clothing simultaneously.
         */
        $lockPath = $path . '.lock';

        $lock = fopen($lockPath, 'c');

        if ($lock === false) {
            abort(500, 'Could not create preview lock.');
        }

        try {
            if (!flock($lock, LOCK_EX)) {
                abort(500, 'Could not lock preview generation.');
            }

            /*
             * Another request may have generated it while we waited.
             */
            clearstatcache(true, $path);

            if (is_file($path) && filesize($path) > 0) {
                return response()->file(
                    $path,
                    [
                        'Content-Type' => 'image/png',
                        'Cache-Control' =>
                            'public, max-age=31536000, immutable',
                    ]
                );
            }

            /*
             * Call Pixinode directly rather than going through
             * Cloudflare/Nginx/the public hostname.
             *
             * Nginx /imaging/ proxies to localhost:8081/.
             */
            try {
                $response = Http::timeout(23)
                    ->connectTimeout(2)
                    ->get(
                        'http://127.0.0.1:8081/avatarimage',
                        [
                            'figure' => $figure,
                            'direction' => 2,
                            'head_direction' => 2,
                            'gesture' => 'sml',
                            'size' => 'l',
                        ]
                    );
            } catch (\Throwable $e) {
                @file_put_contents(
                    $path . '.failed',
                    'TIMEOUT_OR_EXCEPTION' .
                    PHP_EOL .
                    $e->getMessage() .
                    PHP_EOL .
                    date('c') .
                    PHP_EOL
                );

                abort(
                    504,
                    'Avatar renderer timed out.'
                );
            }

            if (!$response->successful()) {
                @file_put_contents(
                    $path . '.failed',
                    'HTTP ' .
                    $response->status() .
                    PHP_EOL .
                    date('c') .
                    PHP_EOL
                );

                abort(
                    502,
                    'Avatar renderer returned HTTP ' .
                    $response->status()
                );
            }

            $body = $response->body();

            /*
             * Do not cache an HTML/JSON error response as a PNG.
             */
            if (
                strlen($body) < 8 ||
                substr($body, 0, 8) !==
                    "\x89PNG\x0D\x0A\x1A\x0A"
            ) {
                @file_put_contents(
                    $path . '.failed',
                    'NOT_PNG' .
                    PHP_EOL .
                    date('c') .
                    PHP_EOL
                );

                abort(
                    502,
                    'Avatar renderer did not return a PNG.'
                );
            }

            $temporary =
                $path .
                '.tmp-' .
                getmypid() .
                '-' .
                bin2hex(random_bytes(4));

            if (
                file_put_contents(
                    $temporary,
                    $body,
                    LOCK_EX
                ) === false
            ) {
                abort(
                    500,
                    'Could not write clothing preview.'
                );
            }

            @chmod($temporary, 0664);

            if (!@rename($temporary, $path)) {
                @unlink($temporary);

                abort(
                    500,
                    'Could not publish clothing preview.'
                );
            }

            if (is_file($failedPath)) {
                @unlink($failedPath);
            }

            return response()->file(
                $path,
                [
                    'Content-Type' => 'image/png',
                    'Cache-Control' =>
                        'public, max-age=31536000, immutable',
                ]
            );
        } finally {
            @flock($lock, LOCK_UN);
            @fclose($lock);

            if (is_file($lockPath)) {
                @unlink($lockPath);
            }
        }
    }

    private function buildPreviewGenerationUrl(
        array $row,
        string $avatarPreview
    ): ?string {
        $setId = (int) ($row['set_id'] ?? 0);

        if ($setId <= 0) {
            return null;
        }

        $parts = parse_url($avatarPreview);

        $query = [];

        parse_str(
            (string) ($parts['query'] ?? ''),
            $query
        );

        $figure = trim(
            (string) ($query['figure'] ?? '')
        );

        if ($figure === '') {
            return null;
        }

        return route(
            'housekeeping.clothing-manager.preview',
            [
                'setId' => $setId,
                'gender' =>
                    $this->previewGenderForRow($row),
                'figure' => $figure,
            ]
        );
    }

    private function resolveCachedAvatarPreviewUrl(
        array &$row,
        string $avatarPreview
    ): ?string {
        $setId = (int) ($row['set_id'] ?? 0);

        if ($setId <= 0) {
            $row['preview_problem'] = 'No preview';
            return null;
        }

        $gender = $this->previewGenderForRow($row);

        $fileName = $this->previewCacheFileName(
            $setId,
            $gender
        );

        $path =
            $this->previewCacheDirectory() .
            DIRECTORY_SEPARATOR .
            $fileName;

        /*
         * Successful preview already generated.
         *
         * Ordinary Clothing Manager browsing only serves static
         * previews. It must never wait for Pixinode.
         */
        if (is_file($path) && filesize($path) > 0) {
            return '/clothing-previews/' . $fileName;
        }

        /*
         * A previous explicit generation attempt failed.
         */
        if (is_file($path . '.failed')) {
            $row['preview_problem'] = 'Render failed';
            return null;
        }

        /*
         * Valid candidate, but not generated yet.
         *
         * Do NOT invoke the renderer from an ordinary manager page.
         */
        $row['preview_problem'] = 'Preview not generated';

        return null;
    }

    private function previewGenderForRow(array $row): string
    {
        $gender = strtoupper(
            trim((string) ($row['gender'] ?? 'U'))
        );

        if ($gender === 'M') {
            return 'm';
        }

        if ($gender === 'F') {
            return 'f';
        }

        /*
         * Must match resolveAvatarPreviewUrl():
         * universal sets deterministically alternate.
         */
        return (
            ((int) ($row['set_id'] ?? 0)) % 2 === 0
        ) ? 'm' : 'f';
    }

    private function previewCacheFileName(
        int $setId,
        string $gender
    ): string {
        return
            $setId .
            '-' .
            strtolower($gender) .
            '.png';
    }

    private function previewCacheDirectory(): string
    {
        return public_path('clothing-previews');
    }

    private function getPreviewBaseLooks(): array
    {
        /*
         * Use actual working hotel looks rather than assuming the
         * installation has a particular set of default Habbo clothes.
         */
        $female = DB::table('users')
            ->where('gender', 'F')
            ->whereNotNull('look')
            ->where('look', '!=', '')
            ->value('look');

        $male = DB::table('users')
            ->where('gender', 'M')
            ->whereNotNull('look')
            ->where('look', '!=', '')
            ->value('look');

        /*
         * These are only emergency fallbacks. Normally the two queries
         * above provide real looks from this installation.
         */
        $female = $female ?: (
            'hr-834-40.' .
            'hd-600-18.' .
            'ch-660-110-1408.' .
            'lg-710-64.' .
            'sh-725-92-1408'
        );

        $male = $male ?: (
            'hr-155-40.' .
            'hd-180-10.' .
            'ch-255-1408.' .
            'lg-280-64.' .
            'sh-290-64'
        );

        /*
         * Clothing previews should use a bare avatar.
         *
         * Keep only the head/skin component from the real hotel look.
         * Existing shirts, trousers, shoes, hair, hats and accessories
         * would otherwise hide the clothing set being inspected.
         */
        $female = $this->makeBarePreviewLook(
            $female,
            'hd-600-18'
        );

        $male = $this->makeBarePreviewLook(
            $male,
            'hd-180-10'
        );

        return [
            'F' => $female,
            'M' => $male,
        ];
    }

    private function makeBarePreviewLook(
        string $look,
        string $fallbackHead
    ): string {
        foreach (explode('.', $look) as $segment) {
            $segment = trim($segment);

            if ($segment === '') {
                continue;
            }

            $type = explode('-', $segment, 2)[0] ?? '';

            /*
             * hd contains the avatar's head/skin definition.
             * The renderer supplies the underlying human body itself,
             * so clothing is intentionally excluded from the base.
             */
            if ($type === 'hd') {
                return $segment;
            }
        }

        return $fallbackHead;
    }

    private function resolveAvatarPreviewUrl(
        array $row,
        array $baseLooks
    ): ?string {
        $endpoint = trim((string) setting('avatar_imager'));

        if ($endpoint === '') {
            return null;
        }

        $type = trim((string) ($row['type'] ?? ''));
        $setId = (int) ($row['set_id'] ?? 0);

        if ($type === '' || $type === '?' || $setId <= 0) {
            return null;
        }

        $gender = strtoupper(
            trim((string) ($row['gender'] ?? 'U'))
        );

        /*
         * Gender-specific clothing uses its matching preview avatar.
         *
         * Universal clothing is deliberately distributed between the
         * male and female preview avatars. Using the set ID keeps the
         * choice deterministic between page loads.
         */
        if ($gender === 'M') {
            $previewGender = 'M';
        } elseif ($gender === 'F') {
            $previewGender = 'F';
        } else {
            $previewGender = ($setId % 2 === 0) ? 'M' : 'F';
        }

        $baseLook = $baseLooks[$previewGender] ?? '';

        if ($baseLook === '') {
            return null;
        }

        $segments = [];

        foreach (explode('.', $baseLook) as $segment) {
            $segment = trim($segment);

            if ($segment === '') {
                continue;
            }

            $segmentType = explode('-', $segment, 2)[0] ?? '';

            /*
             * Remove an existing set of the same FigureData type so the
             * selected clothing is what the avatar renderer receives.
             */
            if ($segmentType === $type) {
                continue;
            }

            $segments[] = $segment;
        }

        $target = $type . '-' . $setId;

        foreach ($row['preview_colors'] ?? [] as $colourId) {
            $colourId = (int) $colourId;

            if ($colourId > 0) {
                $target .= '-' . $colourId;
            }
        }

        $segments[] = $target;

        $look = implode('.', $segments);

        return
            $endpoint .
            $look .
            '&direction=2' .
            '&head_direction=2' .
            '&gesture=sml' .
            '&size=l';
    }

    private function resolvePreviewUrl(
        string $className,
        int $spriteId
    ): ?string {
        $iconDirectory = '/var/www/gamedata/icons';

        $candidates = [
            $className . '_icon.png',
            $className . '.png',
        ];

        if ($spriteId > 0) {
            $candidates[] = $spriteId . '_icon.png';
            $candidates[] = $spriteId . '.png';
        }

        foreach ($candidates as $filename) {
            if (is_file($iconDirectory . '/' . $filename)) {
                return '/gamedata/icons/' . rawurlencode($filename);
            }
        }

        /*
         * Fall back to a case-insensitive filename lookup. This is only
         * performed when the normal names above do not exist.
         */
        foreach (@scandir($iconDirectory) ?: [] as $filename) {
            foreach ($candidates as $candidate) {
                if (strcasecmp($filename, $candidate) === 0) {
                    return '/gamedata/icons/' . rawurlencode($filename);
                }
            }
        }

        return null;
    }

    private function parseSetIds($value): array
    {
        preg_match_all(
            '/\d+/',
            (string) $value,
            $matches
        );

        $ids = [];

        foreach ($matches[0] ?? [] as $raw) {
            $id = (int) $raw;

            if ($id > 0) {
                $ids[$id] = true;
            }
        }

        return array_keys($ids);
    }

    private function humaniseLibraryId(string $id): string
    {
        $value = preg_replace(
            '/^(hair|shirt|trousers|shoes|acc_[a-z]+|jacket|face|hat)_[MFU]_?/i',
            '',
            $id
        );

        $value = preg_replace(
            '/[_\-]+/',
            ' ',
            (string) $value
        );

        $value = preg_replace(
            '/([a-z])([A-Z])/',
            '$1 $2',
            (string) $value
        );

        $value = trim((string) $value);

        return $value !== ''
            ? ucwords($value)
            : $id;
    }

    private function readJson(string $path): array
    {
        if (!is_file($path)) {
            throw new RuntimeException(
                "Required JSON file not found: {$path}"
            );
        }

        $raw = file_get_contents($path);

        if ($raw === false) {
            throw new RuntimeException(
                "Could not read {$path}"
            );
        }

        return json_decode(
            $raw,
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    private function figureDataPath(): string
    {
        return (string) env(
            'FIGUREDATA_PATH',
            self::DEFAULT_FIGUREDATA
        );
    }

    private function figureMapPath(): string
    {
        return (string) env(
            'FIGUREMAP_PATH',
            self::DEFAULT_FIGUREMAP
        );
    }

    private function furnitureDataPath(): string
    {
        return (string) env(
            'FURNIDATA_PATH',
            self::DEFAULT_FURNIDATA
        );
    }
}
