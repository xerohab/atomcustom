<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solace ASE - Furniture Name Editor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: {
                            300:'#fde047',
                            400:'#facc15',
                            500:'#eab308',
                            600:'#ca8a04'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family:'Plus Jakarta Sans',sans-serif;
            background:#0f0507 url('https://solacehotel.pw/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png') no-repeat center center fixed;
            background-size:cover;
        }
    </style>
</head>
<body class="min-h-screen text-white relative antialiased">
<div class="fixed inset-0 bg-black/75 backdrop-blur-[2px] z-0"></div>

<div class="relative z-10">
    <header class="w-full bg-zinc-950/90 border-b border-amber-500/30 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ url('/housekeeping') }}" class="flex items-center gap-2">
                <img src="https://solacehotel.pw/assets/images/Solace.png" alt="Solace Logo" class="h-8 w-auto">
                <span class="text-xs font-black uppercase tracking-widest text-amber-400 bg-red-950 border border-amber-500/40 px-2.5 py-1 rounded-lg">ASE</span>
            </a>

            <a href="{{ url('/housekeeping') }}" class="px-3 py-1.5 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 text-xs font-bold">
                &larr; Back to Dashboard
            </a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8 space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-950/90 border border-emerald-500/40 text-emerald-300 font-bold text-xs">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-2xl bg-red-950/90 border border-red-500/40 text-red-200 font-bold text-xs">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-2xl bg-red-950/90 border border-red-500/40 text-red-200 text-xs space-y-1">
                @foreach($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <section class="bg-zinc-900/95 border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 shadow-2xl">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-amber-500/20 pb-5 mb-6">
                <div>
                    <h1 class="text-2xl font-extrabold text-amber-400">Furniture Name Editor</h1>
                    <p class="text-xs text-zinc-400 mt-1">
                        Select a catalogue page, find the furniture, then change only its friendly display name.
                        Class names and asset filenames are kept untouched.
                    </p>
                </div>

                <div class="text-[11px] text-zinc-400 bg-black/50 border border-amber-500/20 rounded-2xl px-4 py-3">
                    <div>Updates: <span class="font-mono text-amber-300">items_base.public_name</span></div>
                    <div>Updates: <span class="font-mono text-amber-300">FurnitureData.json → name</span></div>
                </div>
            </div>

            <form method="GET" action="{{ route('housekeeping.furniture-name-editor') }}" class="grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-3 items-end">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-amber-300">Catalogue page</label>

                    <input
                        id="pageSearch"
                        type="text"
                        list="pageOptions"
                        autocomplete="off"
                        placeholder="Type a page name or ID..."
                        value="{{ $selectedPage ? $selectedPage->caption . ' (' . $selectedPage->id . ')' : '' }}"
                        class="w-full px-4 py-3 rounded-xl bg-zinc-950 border border-amber-500/30 text-white text-xs"
                    >

                    <input type="hidden" name="page" id="pageId" value="{{ $selectedPage->id ?? '' }}">

                    <datalist id="pageOptions">
                        @foreach($pages as $page)
                            <option value="{{ $page->caption }} ({{ $page->id }})" data-id="{{ $page->id }}"></option>
                        @endforeach
                    </datalist>
                </div>

                <button type="submit" class="px-6 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-zinc-950 font-black text-xs uppercase tracking-wider">
                    Load Furniture
                </button>
            </form>
        </section>

        @if($selectedPage)
            <section class="bg-zinc-900/95 border border-amber-500/30 rounded-3xl p-6 shadow-xl space-y-5">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div>
                        <h2 class="font-extrabold text-amber-400">
                            {{ $selectedPage->caption }}
                            <span class="text-zinc-500 font-mono">#{{ $selectedPage->id }}</span>
                        </h2>
                        <p class="text-xs text-zinc-500 mt-1">
                            {{ $items->count() }} resolved catalogue item entr{{ $items->count() === 1 ? 'y' : 'ies' }}
                        </p>
                    </div>

                    <input
                        id="itemFilter"
                        type="text"
                        placeholder="Filter loaded items..."
                        class="w-full md:w-80 px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-700 focus:border-amber-500/50 text-white text-xs outline-none"
                    >
                </div>

                @if($items->isEmpty())
                    <div class="rounded-2xl bg-black/40 border border-zinc-800 p-8 text-center text-sm text-zinc-500">
                        No catalogue items were found on this page.
                    </div>
                @else
                    <div class="overflow-x-auto rounded-2xl border border-zinc-800">
                        <table class="w-full min-w-[980px] text-xs">
                            <thead class="bg-black/60 text-zinc-400 uppercase tracking-wider">
                                <tr>
                                    <th class="text-left px-4 py-3">Icon</th>
                                    <th class="text-left px-4 py-3">Furniture ID</th>
                                    <th class="text-left px-4 py-3">Display Name</th>
                                    <th class="text-left px-4 py-3">Class Name</th>
                                    <th class="text-left px-4 py-3">Catalog Row</th>
                                    <th class="text-left px-4 py-3">Type</th>
                                    <th class="text-right px-4 py-3">Action</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-zinc-800">
                                @foreach($items as $row)
                                    <tr
                                        class="item-row bg-black/30 hover:bg-black/45 transition"
                                        data-search="{{ strtolower(($row->public_name ?? '') . ' ' . ($row->item_name ?? '') . ' ' . $row->item_id . ' ' . $row->catalog_item_id) }}"
                                    >
                                        <td class="px-4 py-3">
                                            @if($row->resolved)
                                                <div class="w-12 h-12 rounded-xl bg-zinc-950 border border-zinc-800 flex items-center justify-center overflow-hidden">
                                                    <img
                                                        src="{{ $iconBase }}/{{ rawurlencode($row->item_name) }}_icon.png"
                                                        alt=""
                                                        class="max-w-10 max-h-10 object-contain"
                                                        onerror="this.style.display='none'"
                                                    >
                                                </div>
                                            @else
                                                <div class="w-12 h-12 rounded-xl bg-red-950/30 border border-red-500/30 flex items-center justify-center text-red-400">!</div>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 font-mono text-zinc-300">{{ $row->item_id }}</td>

                                        <td class="px-4 py-3">
                                            @if($row->resolved)
                                                <div class="font-bold text-amber-200">{{ $row->public_name }}</div>
                                            @else
                                                <div class="font-bold text-red-300">Missing items_base row</div>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 font-mono text-zinc-400">
                                            {{ $row->item_name ?? $row->catalog_name }}
                                        </td>

                                        <td class="px-4 py-3 font-mono text-zinc-500">#{{ $row->catalog_item_id }}</td>

                                        <td class="px-4 py-3">
                                            @if($row->resolved)
                                                <span class="px-2 py-1 rounded-lg bg-zinc-950 border border-zinc-700 text-zinc-300">
                                                    {{ $row->type === 'i' ? 'Wall' : 'Floor' }}
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 text-right">
                                            @if($row->resolved)
                                                <form
                                                    method="POST"
                                                    action="{{ route('housekeeping.furniture-name-editor.update', ['item' => $row->item_id]) }}"
                                                    class="rename-form"
                                                >
                                                    @csrf
                                                    <input type="hidden" name="catalog_item_id" value="{{ $row->catalog_item_id }}">
                                                    <input type="hidden" name="page_id" value="{{ $selectedPage->id }}">

                                                    <div class="display-actions">
                                                        <button
                                                            type="button"
                                                            class="edit-button px-4 py-2 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 font-bold"
                                                        >
                                                            Edit
                                                        </button>
                                                    </div>

                                                    <div class="edit-actions hidden flex items-center justify-end gap-2">
                                                        <input
                                                            type="text"
                                                            name="public_name"
                                                            value="{{ $row->public_name }}"
                                                            maxlength="56"
                                                            required
                                                            class="rename-input w-72 px-3 py-2 rounded-xl bg-zinc-950 border border-amber-500/40 text-white text-xs outline-none"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="px-3 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-600 text-white font-bold"
                                                        >
                                                            Save
                                                        </button>

                                                        <button
                                                            type="button"
                                                            class="cancel-button px-3 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-200 font-bold"
                                                        >
                                                            Cancel
                                                        </button>
                                                    </div>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <p class="text-[10px] text-zinc-500">
                        Display names are limited to 56 characters because that is the current size of
                        <span class="font-mono">items_base.public_name</span>.
                    </p>
                @endif
            </section>
        @endif
    </main>
</div>

<script>
    const pageSearch = document.getElementById('pageSearch');
    const pageId = document.getElementById('pageId');
    const pageOptions = Array.from(document.querySelectorAll('#pageOptions option'));

    function syncPageId() {
        const exact = pageOptions.find(option => option.value === pageSearch.value);

        if (exact) {
            pageId.value = exact.dataset.id;
            return;
        }

        const typed = pageSearch.value.trim();
        if (/^\d+$/.test(typed)) {
            pageId.value = typed;
            return;
        }

        const match = typed.match(/\((\d+)\)\s*$/);
        pageId.value = match ? match[1] : '';
    }

    if (pageSearch) {
        pageSearch.addEventListener('input', syncPageId);
        pageSearch.addEventListener('change', syncPageId);
    }

    document.querySelectorAll('.rename-form').forEach(form => {
        const displayActions = form.querySelector('.display-actions');
        const editActions = form.querySelector('.edit-actions');
        const editButton = form.querySelector('.edit-button');
        const cancelButton = form.querySelector('.cancel-button');
        const input = form.querySelector('.rename-input');
        const original = input ? input.value : '';

        editButton?.addEventListener('click', () => {
            displayActions.classList.add('hidden');
            editActions.classList.remove('hidden');
            input.focus();
            input.select();
        });

        cancelButton?.addEventListener('click', () => {
            input.value = original;
            editActions.classList.add('hidden');
            displayActions.classList.remove('hidden');
        });
    });

    const itemFilter = document.getElementById('itemFilter');
    itemFilter?.addEventListener('input', () => {
        const needle = itemFilter.value.trim().toLowerCase();

        document.querySelectorAll('.item-row').forEach(row => {
            row.style.display = !needle || row.dataset.search.includes(needle)
                ? ''
                : 'none';
        });
    });
</script>
</body>
</html>
