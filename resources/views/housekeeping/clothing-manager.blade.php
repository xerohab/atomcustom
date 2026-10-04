<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Solace ASE - Clothing Manager</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: {
                            300: '#fde047',
                            400: '#facc15',
                            500: '#eab308',
                            600: '#ca8a04'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background:
                radial-gradient(circle at top, rgba(127,29,29,.23), transparent 36rem),
                #09090b;
        }

        select option {
            background: #18181b;
            color: #fff;
        }

        .ase-scroll::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .ase-scroll::-webkit-scrollbar-thumb {
            background: #78350f;
            border-radius: 999px;
        }

        .ase-scroll::-webkit-scrollbar-track {
            background: #09090b;
        }
    </style>
</head>

<body class="min-h-screen text-zinc-100">

<div class="fixed inset-0 bg-black/60 backdrop-blur-[1px] -z-10"></div>

<header class="w-full bg-zinc-950/95 border-b border-amber-500/30 sticky top-0 z-50">
    <div class="max-w-[1700px] mx-auto px-4 py-3 flex items-center justify-between">
        <a href="{{ url('/housekeeping') }}" class="flex items-center gap-2">
            <img
                src="https://solacehotel.pw/assets/images/Solace.png"
                alt="Solace Logo"
                class="h-8 w-auto"
            >

            <span class="text-xs font-black uppercase tracking-widest text-amber-400 bg-red-950 border border-amber-500/40 px-2.5 py-1 rounded-lg">
                ASE
            </span>
        </a>

        <a
            href="{{ url('/housekeeping') }}"
            class="px-3 py-1.5 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 text-xs font-bold"
        >
            &larr; Back to Dashboard
        </a>
    </div>
</header>

<main class="max-w-[1700px] mx-auto px-4 py-7 space-y-5">

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-950 border border-emerald-500/40 text-emerald-300 text-xs font-bold">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-red-950 border border-red-500/50 text-red-200 text-xs font-bold">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-red-950 border border-red-500/50 text-red-200 text-xs space-y-1">
            @foreach($errors->all() as $error)
                <div>• {{ $error }}</div>
            @endforeach
        </div>
    @endif


    <section class="bg-zinc-900/95 border-2 border-amber-500/40 rounded-3xl p-6 shadow-2xl">

        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-5">
            <div>
                <h1 class="text-2xl font-extrabold text-amber-400">
                    Clothing Library Manager
                </h1>

                <p class="text-xs text-zinc-400 mt-1 max-w-3xl">
                    Manage current FigureData clothing, switch clothing between free and purchasable,
                    choose catalogue destinations and control credit / diamond prices.
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-center text-[11px]">
                <div class="bg-black/50 border border-zinc-700 rounded-xl px-4 py-2">
                    <div class="text-lg font-black text-white">{{ number_format($stats['total']) }}</div>
                    <div class="text-zinc-500">Total sets</div>
                </div>

                <div class="bg-black/50 border border-emerald-800 rounded-xl px-4 py-2">
                    <div class="text-lg font-black text-emerald-400">{{ number_format($stats['free']) }}</div>
                    <div class="text-zinc-500">Free</div>
                </div>

                <div class="bg-black/50 border border-amber-700 rounded-xl px-4 py-2">
                    <div class="text-lg font-black text-amber-400">{{ number_format($stats['sellable']) }}</div>
                    <div class="text-zinc-500">Purchasable</div>
                </div>

                <div class="bg-black/50 border border-sky-800 rounded-xl px-4 py-2">
                    <div class="text-lg font-black text-sky-400">{{ number_format($stats['mapped']) }}</div>
                    <div class="text-zinc-500">Mapped</div>
                </div>

                <div class="bg-black/50 border border-purple-800 rounded-xl px-4 py-2">
                    <div class="text-lg font-black text-purple-400">{{ number_format($stats['ready']) }}</div>
                    <div class="text-zinc-500">Sale ready</div>
                </div>
            </div>
        </div>

        @if(!$figureDataWritable)
            <div class="mt-5 p-4 rounded-2xl bg-red-950/80 border border-red-500/50 text-red-200 text-xs">
                <strong>Write protection:</strong>
                PHP cannot currently write to the FigureData directory.
                Browsing works, but changes will fail until the directory permissions are corrected.
            </div>
        @endif
    </section>


    <section class="bg-zinc-900/95 border border-amber-500/30 rounded-3xl p-5">
        <form
            method="GET"
            action="{{ route('housekeeping.clothing-manager') }}"
            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-3"
        >
            <div class="xl:col-span-2">
                <label class="block text-[11px] font-bold text-amber-300 mb-1">
                    Search
                </label>

                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Name, class, library or set ID..."
                    class="w-full px-3 py-2.5 rounded-xl bg-black/50 border border-amber-500/25 text-white text-xs"
                >
            </div>

            <div>
                <label class="block text-[11px] font-bold text-amber-300 mb-1">
                    Body Type
                </label>

                <select
                    name="type"
                    class="w-full px-3 py-2.5 rounded-xl bg-black/50 border border-amber-500/25 text-white text-xs"
                >
                    <option value="">All types</option>

                    @foreach($types as $type)
                        <option
                            value="{{ $type }}"
                            @selected(request('type') === $type)
                        >
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-amber-300 mb-1">
                    Gender
                </label>

                <select
                    name="gender"
                    class="w-full px-3 py-2.5 rounded-xl bg-black/50 border border-amber-500/25 text-white text-xs"
                >
                    <option value="">All</option>
                    <option value="M" @selected(request('gender') === 'M')>Male</option>
                    <option value="F" @selected(request('gender') === 'F')>Female</option>
                    <option value="U" @selected(request('gender') === 'U')>Unisex</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-amber-300 mb-1">
                    Availability
                </label>

                <select
                    name="state"
                    class="w-full px-3 py-2.5 rounded-xl bg-black/50 border border-amber-500/25 text-white text-xs"
                >
                    <option value="">All</option>
                    <option value="free" @selected(request('state') === 'free')>Free</option>
                    <option value="purchasable" @selected(request('state') === 'purchasable')>Purchasable</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-amber-300 mb-1">
                    Mapping
                </label>

                <select
                    name="mapping"
                    class="w-full px-3 py-2.5 rounded-xl bg-black/50 border border-amber-500/25 text-white text-xs"
                >
                    <option value="">All</option>
                    <option value="ready" @selected(request('mapping') === 'ready')>Sale ready</option>
                    <option value="mapped" @selected(request('mapping') === 'mapped')>Has wrapper</option>
                    <option value="broken" @selected(request('mapping') === 'broken')>Broken wrapper</option>
                    <option value="unmapped" @selected(request('mapping') === 'unmapped')>No wrapper</option>
                </select>
            </div>

            <div class="xl:col-span-6 flex justify-end gap-2">
                <a
                    href="{{ route('housekeeping.clothing-manager') }}"
                    class="px-4 py-2 rounded-xl bg-zinc-800 border border-zinc-600 text-zinc-300 text-xs font-bold"
                >
                    Reset
                </a>

                <button
                    type="submit"
                    class="px-5 py-2 rounded-xl bg-gradient-to-r from-red-900 to-amber-700 border border-amber-400/40 text-amber-100 text-xs font-bold"
                >
                    Search / Filter
                </button>
            </div>
        </form>
    </section>


    <form
        method="POST"
        action="{{ route('housekeeping.clothing-manager.apply') }}"
        id="clothingForm"
        class="space-y-4"
    >
        @csrf

        <section class="bg-zinc-900/95 border border-amber-500/30 rounded-3xl p-5">
            <div class="flex flex-col xl:flex-row gap-4 xl:items-end">

                <div>
                    <label class="block text-[11px] font-bold text-amber-300 mb-1">
                        Selected Action
                    </label>

                    <select
                        name="mode"
                        id="mode"
                        class="w-52 px-3 py-2.5 rounded-xl bg-black/50 border border-amber-500/25 text-white text-xs"
                    >
                        <option value="purchasable">Make Purchasable</option>
                        <option value="free">Make Free</option>
                    </select>
                </div>

                <div class="flex-1 min-w-[260px] purchase-option">
                    <label class="block text-[11px] font-bold text-amber-300 mb-1">
                        Catalogue Page
                    </label>

                    <select
                        name="page_id"
                        class="w-full px-3 py-2.5 rounded-xl bg-black/50 border border-amber-500/25 text-white text-xs"
                    >
                        <option value="">Choose destination...</option>

                        @foreach($pages as $page)
                            <option
                                value="{{ $page->id }}"
                                @selected((string) old('page_id') === (string) $page->id)
                            >
                                {{ $page->caption }}
                                — #{{ $page->id }}
                                @if((int) $page->min_rank > 1)
                                    — rank {{ $page->min_rank }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="purchase-option">
                    <label class="block text-[11px] font-bold text-amber-300 mb-1">
                        Coins
                    </label>

                    <input
                        name="credits"
                        type="number"
                        min="0"
                        value="{{ old('credits', 3) }}"
                        class="w-28 px-3 py-2.5 rounded-xl bg-black/50 border border-amber-500/25 text-white text-xs"
                    >
                </div>

                <div class="purchase-option">
                    <label class="block text-[11px] font-bold text-amber-300 mb-1">
                        Diamonds
                    </label>

                    <input
                        name="diamonds"
                        type="number"
                        min="0"
                        value="{{ old('diamonds', 0) }}"
                        class="w-28 px-3 py-2.5 rounded-xl bg-black/50 border border-amber-500/25 text-white text-xs"
                    >
                </div>

                <div class="flex items-center gap-2">
                    <span
                        id="selectedCount"
                        class="text-xs text-zinc-400 min-w-24"
                    >
                        0 selected
                    </span>

                    <button
                        type="submit"
                        onclick="return confirmClothingAction()"
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-900 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 text-amber-100 text-xs font-black uppercase tracking-wide"
                    >
                        Apply
                    </button>
                </div>
            </div>

            <div class="mt-3 text-[11px] text-zinc-500">
                Making an item purchasable is only allowed when Clothing Manager can positively identify a complete
                <span class="text-amber-300">specialtype 23</span>
                furniture wrapper and its
                <span class="text-amber-300">items_base</span>
                record.
            </div>
        </section>


        <section class="bg-zinc-900/95 border border-amber-500/30 rounded-3xl overflow-hidden">

            <div class="px-5 py-4 border-b border-amber-500/20 flex items-center justify-between gap-3">
                <div>
                    <h2 class="font-black text-amber-400">
                        Current Clothing
                    </h2>

                    <div class="text-[11px] text-zinc-500 mt-1">
                        Showing {{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }}
                        of {{ number_format($items->total()) }} matching sets
                    </div>
                </div>

                <label class="flex items-center gap-2 text-xs text-zinc-300 cursor-pointer">
                    <input
                        id="selectAll"
                        type="checkbox"
                        class="rounded border-amber-500"
                    >
                    Select page
                </label>
            </div>

            <div class="overflow-x-auto ase-scroll">
                <table class="w-full text-left text-xs min-w-[1450px]">
                    <thead class="bg-black/40 text-[10px] uppercase tracking-wider text-amber-300">
                        <tr>
                            <th class="p-3 w-12"></th>
                            <th class="p-3">Preview</th>
                            <th class="p-3">Clothing</th>
                            <th class="p-3">Set</th>
                            <th class="p-3">Type</th>
                            <th class="p-3">Gender</th>
                            <th class="p-3">State</th>
                            <th class="p-3">Wrapper</th>
                            <th class="p-3">Catalogue</th>
                            <th class="p-3">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-800">
                        @forelse($items as $item)
                            <tr class="hover:bg-amber-950/10">
                                <td class="p-3 align-top">
                                    <input
                                        type="checkbox"
                                        name="selected[]"
                                        value="{{ $item['set_id'] }}"
                                        class="itemCheckbox rounded border-amber-500"
                                    >
                                </td>

                                <td class="p-3 align-top">
                                    @if(!empty($item['preview_url']))
                                        @php
                                            $rawPreviewUrl = (string) $item['preview_url'];

                                            $previewUrl =
                                                str_starts_with($rawPreviewUrl, 'http://') ||
                                                str_starts_with($rawPreviewUrl, 'https://')
                                                    ? $rawPreviewUrl
                                                    : url($rawPreviewUrl);

                                            $fallbackUrl = '';

                                            if (!empty($item['icon_preview_url'])) {
                                                $rawFallbackUrl =
                                                    (string) $item['icon_preview_url'];

                                                $fallbackUrl =
                                                    str_starts_with($rawFallbackUrl, 'http://') ||
                                                    str_starts_with($rawFallbackUrl, 'https://')
                                                        ? $rawFallbackUrl
                                                        : url($rawFallbackUrl);
                                            }
                                        @endphp

                                        <div
                                            class="w-24 h-28 rounded-2xl bg-black/60 border border-amber-500/20 flex items-center justify-center overflow-hidden relative"
                                        >
                                            <img
                                                src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="
                                                data-preview-src="{{ $previewUrl }}"
                                                data-fallback="{{ $fallbackUrl }}"
                                                data-preview-state="waiting"
                                                alt="{{ $item['label'] }}"
                                                title="{{ $item['label'] }} — Set {{ $item['set_id'] }}"
                                                class="clothingPreview max-w-[92px] max-h-[108px] object-contain opacity-0 transition-opacity duration-200"
                                                style="image-rendering: pixelated;"
                                            >

                                            <div
                                                style="display:none"
                                                class="w-full h-full items-center justify-center text-center text-[9px] text-zinc-600 px-2"
                                            >
                                                {{ $item['preview_problem'] ?? 'No preview' }}
                                            </div>
                                        </div>
                                    @else
                                        <div class="w-24 h-28 rounded-2xl bg-black/40 border border-zinc-800 flex items-center justify-center text-center text-[9px] text-zinc-600 px-2">
                                            {{ $item['preview_problem'] ?? 'No preview' }}
                                        </div>
                                    @endif
                                </td>

                                <td class="p-3 align-top">
                                    <div class="font-bold text-white">
                                        {{ $item['label'] }}
                                    </div>

                                    @if($item['library_ids'])
                                        <div class="text-[10px] text-zinc-500 font-mono mt-1 max-w-[320px] break-all">
                                            {{ implode(', ', array_slice($item['library_ids'], 0, 3)) }}
                                        </div>
                                    @endif
                                </td>

                                <td class="p-3 align-top">
                                    <span class="font-mono text-amber-300">
                                        {{ $item['set_id'] }}
                                    </span>
                                </td>

                                <td class="p-3 align-top">
                                    <span class="font-mono text-sky-300">
                                        {{ $item['type'] }}
                                    </span>
                                </td>

                                <td class="p-3 align-top">
                                    {{ $item['gender'] }}
                                </td>

                                <td class="p-3 align-top">
                                    @if($item['sellable'])
                                        <span class="px-2 py-1 rounded-lg bg-amber-950 border border-amber-600/40 text-amber-300 text-[10px] font-bold">
                                            PURCHASABLE
                                        </span>
                                    @else
                                        <span class="px-2 py-1 rounded-lg bg-emerald-950 border border-emerald-600/40 text-emerald-300 text-[10px] font-bold">
                                            FREE
                                        </span>
                                    @endif
                                </td>

                                <td class="p-3 align-top max-w-[360px]">
                                    @if($item['preferred_wrapper'])
                                        <div class="text-white font-semibold">
                                            {{ $item['preferred_wrapper']['name'] }}
                                        </div>

                                        <div class="text-[10px] text-zinc-500 font-mono break-all">
                                            {{ $item['preferred_wrapper']['classname'] }}
                                        </div>

                                        @if(count($item['preferred_wrapper']['set_ids']) > 1)
                                            <div class="text-[10px] text-sky-300 mt-1">
                                                Product sets:
                                                {{ implode(', ', $item['preferred_wrapper']['set_ids']) }}
                                            </div>
                                        @endif

                                    @elseif($item['wrapper_classes'])
                                        <div class="text-orange-300 text-[10px] font-bold">
                                            Wrapper found but incomplete
                                        </div>

                                        <div class="text-[10px] text-zinc-500 font-mono">
                                            {{ implode(', ', array_slice($item['wrapper_classes'], 0, 3)) }}
                                        </div>

                                    @else
                                        <span class="text-zinc-600">
                                            No wrapper mapping
                                        </span>
                                    @endif
                                </td>

                                <td class="p-3 align-top">
                                    @if($item['catalog'])
                                        <div class="text-emerald-300 font-bold">
                                            Item #{{ $item['catalog']['id'] }}
                                        </div>

                                        <div class="text-zinc-400 text-[10px]">
                                            Page #{{ $item['catalog']['page_id'] }}
                                        </div>

                                        <div class="text-zinc-400 text-[10px]">
                                            {{ $item['catalog']['credits'] }} coins
                                            /
                                            {{ $item['catalog']['diamonds'] }} diamonds
                                        </div>
                                    @else
                                        <span class="text-zinc-600">
                                            No catalogue row
                                        </span>
                                    @endif
                                </td>

                                <td class="p-3 align-top">
                                    @if($item['can_purchase'])
                                        <span class="text-purple-300 font-bold text-[10px]">
                                            SALE READY
                                        </span>
                                    @elseif($item['wrapper_classes'])
                                        <span class="text-orange-300 font-bold text-[10px]">
                                            NEEDS REPAIR
                                        </span>
                                    @else
                                        <span class="text-zinc-500 text-[10px]">
                                            FREE ONLY FOR NOW
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="p-8 text-center text-zinc-500">
                                    No clothing matched these filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-amber-500/20">
                {{ $items->links() }}
            </div>
        </section>
    </form>


<section
    id="previewCacheGenerator"
    class="max-w-[1700px] mx-auto px-4 pb-8"
>
    <div class="rounded-2xl border border-amber-500/20 bg-zinc-950/80 overflow-hidden">
        <div class="px-5 py-4 border-b border-amber-500/20 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="text-xs uppercase tracking-[0.25em] text-amber-400 font-bold">
                    Clothing Preview Cache
                </div>

                <div class="text-sm text-zinc-400 mt-1">
                    Generate previews separately from normal Clothing Manager browsing.
                    Cached previews load instantly on future visits.
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    id="generateCurrentPage"
                    class="px-4 py-2 rounded-xl bg-amber-500 text-black font-black text-xs hover:bg-amber-400"
                >
                    Generate Current Page
                </button>

                <button
                    type="button"
                    id="generateAllMissing"
                    class="px-4 py-2 rounded-xl bg-red-700 text-white font-black text-xs hover:bg-red-600"
                >
                    Generate All Missing
                </button>

                <button
                    type="button"
                    id="retryFailedPreviews"
                    class="px-4 py-2 rounded-xl bg-zinc-800 border border-zinc-700 text-zinc-200 font-bold text-xs hover:bg-zinc-700"
                >
                    Retry Failed
                </button>

                <button
                    type="button"
                    id="stopPreviewGeneration"
                    disabled
                    class="px-4 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-500 font-bold text-xs disabled:opacity-40"
                >
                    Stop
                </button>
            </div>
        </div>

        <div class="p-5">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-5">
                <div class="rounded-xl bg-black/50 border border-zinc-800 p-3">
                    <div class="text-[10px] uppercase text-zinc-500">Cached</div>
                    <div id="previewCountCached" class="text-xl font-black text-emerald-400">—</div>
                </div>

                <div class="rounded-xl bg-black/50 border border-zinc-800 p-3">
                    <div class="text-[10px] uppercase text-zinc-500">Not Generated</div>
                    <div id="previewCountPending" class="text-xl font-black text-amber-400">—</div>
                </div>

                <div class="rounded-xl bg-black/50 border border-zinc-800 p-3">
                    <div class="text-[10px] uppercase text-zinc-500">Failed</div>
                    <div id="previewCountFailed" class="text-xl font-black text-red-400">—</div>
                </div>

                <div class="rounded-xl bg-black/50 border border-zinc-800 p-3">
                    <div class="text-[10px] uppercase text-zinc-500">Missing Asset</div>
                    <div id="previewCountMissing" class="text-xl font-black text-red-300">—</div>
                </div>

                <div class="rounded-xl bg-black/50 border border-zinc-800 p-3">
                    <div class="text-[10px] uppercase text-zinc-500">Unavailable</div>
                    <div id="previewCountUnavailable" class="text-xl font-black text-zinc-400">—</div>
                </div>
            </div>

            <div class="h-3 rounded-full bg-black border border-zinc-800 overflow-hidden">
                <div
                    id="previewGeneratorBar"
                    class="h-full bg-amber-500 transition-all duration-200"
                    style="width:0%"
                ></div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mt-3">
                <div
                    id="previewGeneratorStatus"
                    class="text-xs text-zinc-400"
                >
                    Ready.
                </div>

                <div
                    id="previewGeneratorProgress"
                    class="text-xs font-mono text-zinc-500"
                >
                    0 / 0
                </div>
            </div>
        </div>
    </div>
</section>

</main>

<script>
    const selectAll = document.getElementById('selectAll');
    const checkboxes = () => Array.from(document.querySelectorAll('.itemCheckbox'));
    const countElement = document.getElementById('selectedCount');
    const mode = document.getElementById('mode');

    function updateSelectedCount()
    {
        const total = checkboxes().filter(box => box.checked).length;
        countElement.textContent = `${total} selected`;
    }

    function updateMode()
    {
        const purchasable = mode.value === 'purchasable';

        document.querySelectorAll('.purchase-option').forEach(el => {
            el.style.opacity = purchasable ? '1' : '.35';

            el.querySelectorAll('input,select').forEach(input => {
                input.disabled = !purchasable;
            });
        });
    }

    selectAll?.addEventListener('change', function () {
        checkboxes().forEach(box => {
            box.checked = this.checked;
        });

        updateSelectedCount();
    });

    checkboxes().forEach(box => {
        box.addEventListener('change', updateSelectedCount);
    });

    mode?.addEventListener('change', updateMode);

    updateSelectedCount();
    updateMode();

    function confirmClothingAction()
    {
        const selected = checkboxes().filter(box => box.checked).length;

        if (!selected) {
            alert('Select at least one clothing set first.');
            return false;
        }

        if (mode.value === 'free') {
            return confirm(
                `Set ${selected} selected clothing set(s) to FREE?\n\n` +
                `They will no longer require ownership to wear.`
            );
        }

        return confirm(
            `Make ${selected} selected clothing set(s) PURCHASABLE?\n\n` +
            `The manager may also include other figure sets belonging to the same clothing product.\n\n` +
            `FigureData will be backed up before it is changed.`
        );
    }
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const images = Array.from(
        document.querySelectorAll(
            'img.clothingPreview[data-preview-src]'
        )
    );

    if (!images.length) {
        return;
    }

    const queue = [];
    const queued = new Set();

    /*
     * Pixinode currently reports render concurrency=1.
     *
     * Never allow the Clothing Manager to send multiple expensive
     * avatar renders at the same time.
     */
    let active = false;

    function showNoPreview(img) {
        img.style.display = 'none';

        const placeholder = img.nextElementSibling;

        if (placeholder) {
            placeholder.style.display = 'flex';
        }
    }

    function runNext() {
        if (active || !queue.length) {
            return;
        }

        const img = queue.shift();

        if (!img) {
            return;
        }

        if (img.dataset.previewState !== 'waiting') {
            window.setTimeout(runNext, 25);
            return;
        }

        const previewSrc = img.dataset.previewSrc;

        if (!previewSrc) {
            img.dataset.previewState = 'failed';
            showNoPreview(img);
            window.setTimeout(runNext, 25);
            return;
        }

        active = true;
        img.dataset.previewState = 'loading';

        const finish = function () {
            active = false;

            /*
             * Pixinode serializes renders itself. Start the next queued
             * preview immediately after this request has completed.
             */
            window.setTimeout(runNext, 0);
        };

        img.onload = function () {
            /*
             * Ignore the initial transparent placeholder.
             */
            if (img.dataset.previewState !== 'loading') {
                return;
            }

            img.dataset.previewState = 'loaded';
            img.classList.remove('opacity-0');

            finish();
        };

        img.onerror = function () {
            const fallback = img.dataset.fallback || '';

            /*
             * Avatar rendering failed. Use a furniture icon if one
             * exists, but do not hold the renderer queue while that
             * simple PNG loads.
             */
            if (fallback) {
                img.dataset.fallback = '';
                img.dataset.previewState = 'fallback';

                img.onload = function () {
                    img.classList.remove('opacity-0');
                };

                img.onerror = function () {
                    showNoPreview(img);
                };

                img.src = fallback;
                finish();

                return;
            }

            img.dataset.previewState = 'failed';
            showNoPreview(img);

            finish();
        };

        img.src = previewSrc;
    }

    function enqueue(img) {
        if (
            !img ||
            img.dataset.previewState !== 'waiting' ||
            queued.has(img)
        ) {
            return;
        }

        queued.add(img);
        queue.push(img);

        runNext();
    }

    /*
     * Render only clothing close to the visible browser viewport.
     */
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    observer.unobserve(entry.target);
                    enqueue(entry.target);
                });
            },
            {
                root: null,
                rootMargin: '180px 0px',
                threshold: 0.01
            }
        );

        images.forEach(function (img) {
            observer.observe(img);
        });
    } else {
        images.forEach(enqueue);
    }
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const manifestUrl =
        @json(route('housekeeping.clothing-manager.preview-manifest'));

    const currentButton =
        document.getElementById('generateCurrentPage');

    const allButton =
        document.getElementById('generateAllMissing');

    const retryButton =
        document.getElementById('retryFailedPreviews');

    const stopButton =
        document.getElementById('stopPreviewGeneration');

    const statusElement =
        document.getElementById('previewGeneratorStatus');

    const progressElement =
        document.getElementById('previewGeneratorProgress');

    const bar =
        document.getElementById('previewGeneratorBar');

    const countCached =
        document.getElementById('previewCountCached');

    const countPending =
        document.getElementById('previewCountPending');

    const countFailed =
        document.getElementById('previewCountFailed');

    const countMissing =
        document.getElementById('previewCountMissing');

    const countUnavailable =
        document.getElementById('previewCountUnavailable');

    let running = false;
    let stopRequested = false;

    function setButtons(isRunning)
    {
        running = isRunning;

        if (currentButton) currentButton.disabled = isRunning;
        if (allButton) allButton.disabled = isRunning;
        if (retryButton) retryButton.disabled = isRunning;
        if (stopButton) stopButton.disabled = !isRunning;
    }

    function updateCounts(counts)
    {
        counts = counts || {};

        if (countCached) {
            countCached.textContent =
                counts.cached ?? 0;
        }

        if (countPending) {
            countPending.textContent =
                counts.pending ?? 0;
        }

        if (countFailed) {
            countFailed.textContent =
                counts.failed ?? 0;
        }

        if (countMissing) {
            countMissing.textContent =
                counts.missing_asset ?? 0;
        }

        if (countUnavailable) {
            countUnavailable.textContent =
                counts.unavailable ?? 0;
        }
    }

    async function loadManifest(mode, setIds = [])
    {
        const url = new URL(
            manifestUrl,
            window.location.origin
        );

        url.searchParams.set('mode', mode);

        if (setIds.length) {
            url.searchParams.set(
                'set_ids',
                setIds.join(',')
            );
        }

        const response = await fetch(
            url.toString(),
            {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            }
        );

        if (!response.ok) {
            throw new Error(
                `Manifest returned HTTP ${response.status}`
            );
        }

        return await response.json();
    }

    async function refreshPreviewCounts()
    {
        try {
            const data = await loadManifest('pending');

            updateCounts(data.counts || {});
        } catch (error) {
            console.error(error);
        }
    }

    function currentPageSetIds()
    {
        return Array.from(
            document.querySelectorAll('.itemCheckbox')
        )
            .map(box => parseInt(box.value || '0', 10))
            .filter(id => id > 0);
    }

    function addQueryParameter(url, name, value)
    {
        const parsed = new URL(
            url,
            window.location.origin
        );

        parsed.searchParams.set(name, value);

        return parsed.toString();
    }

    async function generateOne(job, retryFailed)
    {
        let url = job.url;

        if (retryFailed) {
            url = addQueryParameter(
                url,
                'retry',
                '1'
            );
        }

        const controller = new AbortController();

        /*
         * Laravel allows ~23 seconds so Pixinode can complete its
         * own ~20 second failure timeout. Browser gets a little more.
         */
        const timer = window.setTimeout(
            () => controller.abort(),
            28000
        );

        try {
            const response = await fetch(
                url,
                {
                    credentials: 'same-origin',
                    signal: controller.signal,
                    cache: 'no-store',
                    headers: {
                        'Accept': 'image/png'
                    }
                }
            );

            if (!response.ok) {
                return {
                    ok: false,
                    status: response.status
                };
            }

            /*
             * Consume the response so the request is fully complete
             * before starting another render.
             */
            await response.blob();

            return {
                ok: true,
                status: response.status
            };
        } catch (error) {
            return {
                ok: false,
                status:
                    error.name === 'AbortError'
                        ? 'timeout'
                        : 'network'
            };
        } finally {
            window.clearTimeout(timer);
        }
    }

    async function startPreviewGeneration(
        mode,
        setIds = []
    ) {
        if (running) {
            return;
        }

        stopRequested = false;
        setButtons(true);

        statusElement.textContent =
            'Building preview job list…';

        bar.style.width = '0%';
        progressElement.textContent = '0 / 0';

        try {
            const manifest = await loadManifest(
                mode,
                setIds
            );

            updateCounts(manifest.counts || {});

            const jobs = manifest.jobs || [];

            if (!jobs.length) {
                statusElement.textContent =
                    mode === 'failed'
                        ? 'No failed previews to retry.'
                        : 'No previews need generating.';

                return;
            }

            const total = jobs.length;

            let complete = 0;
            let successful = 0;
            let failed = 0;

            for (const job of jobs) {
                if (stopRequested) {
                    statusElement.textContent =
                        `Stopped. ${successful} generated, ` +
                        `${failed} failed.`;

                    break;
                }

                statusElement.textContent =
                    `Rendering ${job.label} ` +
                    `(Set ${job.set_id})…`;

                const result = await generateOne(
                    job,
                    mode === 'failed'
                );

                complete++;

                if (result.ok) {
                    successful++;
                } else {
                    failed++;
                }

                const percent =
                    Math.round(
                        (complete / total) * 100
                    );

                bar.style.width = `${percent}%`;

                progressElement.textContent =
                    `${complete} / ${total}`;

                if (!stopRequested) {
                    statusElement.textContent =
                        `${successful} generated, ` +
                        `${failed} failed — ` +
                        `${complete} / ${total}`;
                }

                /*
                 * Tiny gap gives the single renderer a clean boundary
                 * between jobs.
                 */
                await new Promise(resolve =>
                    window.setTimeout(resolve, 150)
                );
            }

            if (!stopRequested) {
                statusElement.textContent =
                    `Finished: ${successful} generated, ` +
                    `${failed} failed. Reload this page to show ` +
                    `new cached previews.`;
            }
        } catch (error) {
            console.error(error);

            statusElement.textContent =
                'Preview generation error: ' +
                error.message;
        } finally {
            setButtons(false);
            await refreshPreviewCounts();
        }
    }

    currentButton?.addEventListener(
        'click',
        function () {
            startPreviewGeneration(
                'pending',
                currentPageSetIds()
            );
        }
    );

    allButton?.addEventListener(
        'click',
        function () {
            if (!confirm(
                'Generate every valid missing clothing preview?\n\n' +
                'This may take a long time because previews are ' +
                'rendered one at a time. You can stop at any time.'
            )) {
                return;
            }

            startPreviewGeneration('pending');
        }
    );

    retryButton?.addEventListener(
        'click',
        function () {
            if (!confirm(
                'Retry every previously failed clothing preview?'
            )) {
                return;
            }

            startPreviewGeneration('failed');
        }
    );

    stopButton?.addEventListener(
        'click',
        function () {
            stopRequested = true;

            statusElement.textContent =
                'Stopping after the current render…';
        }
    );

    refreshPreviewCounts();
});
</script>

</body>
</html>
