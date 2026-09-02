<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Lounge ASE - Clothing Uploader</title>

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

        .drop-active {
            border-color: rgb(251 191 36) !important;
            background: rgba(120, 53, 15, .25) !important;
        }
    </style>
</head>

<body class="min-h-screen text-zinc-100">

<div class="fixed inset-0 bg-black/60 backdrop-blur-[1px] -z-10"></div>

<header class="w-full bg-zinc-950/95 border-b border-amber-500/30 sticky top-0 z-50">
    <div class="max-w-[1700px] mx-auto px-4 py-3 flex items-center justify-between">
        <a href="{{ url('/housekeeping') }}" class="flex items-center gap-2">
            <img
                src="https://loungehotel.org/assets/images/lounge.png"
                alt="Lounge Logo"
                class="h-8 w-auto"
            >

            <span class="text-xs font-black uppercase tracking-widest text-amber-400 bg-red-950 border border-amber-500/40 px-2.5 py-1 rounded-lg">
                ASE
            </span>
        </a>

        <div class="flex gap-2">
            <a
                href="{{ route('housekeeping.clothing-manager') }}"
                class="px-3 py-1.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-600 text-zinc-300 text-xs font-bold"
            >
                Clothing Library
            </a>

            <a
                href="{{ url('/housekeeping') }}"
                class="px-3 py-1.5 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 text-xs font-bold"
            >
                &larr; Dashboard
            </a>
        </div>
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
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-amber-400">
                    Clothing Uploader
                </h1>

                <p class="text-xs text-zinc-400 mt-1 max-w-4xl">
                    Upload one or multiple SWF / Nitro avatar clothing assets.
                    The clothing worker will validate, convert and install the asset,
                    register its FigureData / FigureMap information and create its catalogue unlock.
                </p>
            </div>

            <div class="text-right text-[11px]">
                <div class="text-zinc-500">
                    Maximum batch
                </div>
                <div class="text-lg font-black text-amber-400">
                    100 files
                </div>
            </div>
        </div>
    </section>

    <form
        method="POST"
        action="{{ route('housekeeping.clothing-uploader.store') }}"
        enctype="multipart/form-data"
        class="space-y-5"
        id="uploadForm"
    >
        @csrf

        <section class="bg-zinc-900/95 border border-amber-500/30 rounded-3xl p-5 space-y-5">

            <div
                id="dropZone"
                class="border-2 border-dashed border-amber-500/30 rounded-3xl p-10 text-center cursor-pointer transition bg-black/20"
            >
                <input
                    type="file"
                    id="clothingFiles"
                    name="clothing[]"
                    accept=".swf,.nitro"
                    multiple
                    class="hidden"
                    required
                >

                <div class="text-4xl mb-3">👕</div>

                <h2 class="font-black text-amber-400">
                    Drop clothing files here
                </h2>

                <p class="text-xs text-zinc-400 mt-2">
                    SWF and Nitro supported. Multiple files can be uploaded together.
                </p>

                <button
                    type="button"
                    id="browseButton"
                    class="mt-4 px-5 py-2 rounded-xl bg-red-950 hover:bg-red-900 border border-amber-500/40 text-amber-300 text-xs font-black"
                >
                    Choose Files
                </button>
            </div>

            <div
                id="fileList"
                class="hidden bg-black/40 border border-zinc-700 rounded-2xl overflow-hidden"
            >
                <div class="px-4 py-3 border-b border-zinc-700 flex items-center justify-between">
                    <span class="text-xs font-black text-amber-300">
                        Selected files
                    </span>

                    <span
                        id="fileCount"
                        class="text-[11px] text-zinc-500"
                    ></span>
                </div>

                <div
                    id="fileRows"
                    class="max-h-72 overflow-y-auto ase-scroll divide-y divide-zinc-800"
                ></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-amber-300 mb-1">
                        Catalogue Page
                    </label>

                    <select
                        name="page_id"
                        required
                        class="w-full px-3 py-2.5 rounded-xl bg-black/50 border border-amber-500/25 text-white text-xs"
                    >
                        <option value="">
                            Choose destination...
                        </option>

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

                <div>
                    <label class="block text-[11px] font-bold text-amber-300 mb-1">
                        Coins
                    </label>

                    <input
                        name="credits"
                        type="number"
                        min="0"
                        value="{{ old('credits', 3) }}"
                        required
                        class="w-full px-3 py-2.5 rounded-xl bg-black/50 border border-amber-500/25 text-white text-xs"
                    >
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-amber-300 mb-1">
                        Points
                    </label>

                    <input
                        name="points"
                        type="number"
                        min="0"
                        value="{{ old('points', 0) }}"
                        required
                        class="w-full px-3 py-2.5 rounded-xl bg-black/50 border border-amber-500/25 text-white text-xs"
                    >
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-amber-300 mb-1">
                        Points Type
                    </label>

                    <input
                        name="points_type"
                        type="number"
                        min="0"
                        value="{{ old('points_type', 0) }}"
                        required
                        class="w-full px-3 py-2.5 rounded-xl bg-black/50 border border-amber-500/25 text-white text-xs"
                    >
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-amber-300 mb-1">
                        Gender
                    </label>

                    <select
                        name="gender"
                        class="w-full px-3 py-2.5 rounded-xl bg-black/50 border border-amber-500/25 text-white text-xs"
                    >
                        <option value="auto" selected>
                            Auto Detect
                        </option>

                        <option value="U">
                            Unisex
                        </option>

                        <option value="M">
                            Male
                        </option>

                        <option value="F">
                            Female
                        </option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    id="uploadButton"
                    class="px-6 py-3 rounded-xl bg-gradient-to-r from-red-900 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 text-amber-100 text-xs font-black uppercase tracking-wide"
                >
                    Upload & Queue Clothing
                </button>
            </div>

        </section>
    </form>

    <section class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        <div class="bg-zinc-900/95 border border-amber-500/30 rounded-3xl overflow-hidden">
            <div class="px-5 py-4 border-b border-amber-500/20">
                <h2 class="font-black text-amber-400">
                    Recent Batches
                </h2>
            </div>

            <div class="divide-y divide-zinc-800">
                @forelse($recentBatches as $batch)
                    <a
                        href="{{ route('housekeeping.clothing-uploader', ['batch' => $batch->id]) }}"
                        class="block p-4 hover:bg-amber-950/10"
                    >
                        <div class="flex justify-between gap-3">
                            <div>
                                <div class="text-xs font-bold text-zinc-200">
                                    Batch #{{ $batch->id }}
                                </div>

                                <div class="text-[10px] text-zinc-500 mt-1">
                                    {{ $batch->target_page_caption }}
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="text-xs font-black text-amber-300">
                                    {{ $batch->file_count }}
                                </div>

                                <div class="text-[9px] text-zinc-500">
                                    files
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-5 text-xs text-zinc-500">
                        No clothing batches yet.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="xl:col-span-2 bg-zinc-900/95 border border-amber-500/30 rounded-3xl overflow-hidden">

            <div class="px-5 py-4 border-b border-amber-500/20">
                <h2 class="font-black text-amber-400">
                    @if($selectedBatch)
                        Batch #{{ $selectedBatch->id }}
                    @else
                        Batch Status
                    @endif
                </h2>

                @if($selectedBatch)
                    <div class="text-[10px] text-zinc-500 mt-1">
                        Target:
                        {{ $selectedBatch->target_page_caption }}
                        (#{{ $selectedBatch->target_page_id }})
                    </div>
                @endif
            </div>

            @if($selectedBatch)
                <div class="overflow-x-auto ase-scroll">
                    <table class="w-full text-left text-xs min-w-[1000px]">
                        <thead class="bg-black/40 text-[10px] uppercase tracking-wider text-amber-300">
                            <tr>
                                <th class="p-3">File</th>
                                <th class="p-3">Source</th>
                                <th class="p-3">Library</th>
                                <th class="p-3">Type</th>
                                <th class="p-3">Gender</th>
                                <th class="p-3">Set</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Message</th>
                            </tr>
                        </thead>

                        <tbody
                            id="jobRows"
                            class="divide-y divide-zinc-800"
                        >
                            @foreach($jobs as $job)
                                <tr data-job="{{ $job->id }}">
                                    <td class="p-3">
                                        {{ $job->original_name }}
                                    </td>

                                    <td class="p-3 uppercase">
                                        {{ $job->source }}
                                    </td>

                                    <td class="p-3 library">
                                        {{ $job->library_name ?? '—' }}
                                    </td>

                                    <td class="p-3 figure-type">
                                        {{ $job->figure_type ?? '—' }}
                                    </td>

                                    <td class="p-3 gender">
                                        {{ $job->gender }}
                                    </td>

                                    <td class="p-3 set-id">
                                        {{ $job->set_id ?? '—' }}
                                    </td>

                                    <td class="p-3 status">
                                        <span class="
                                            @if($job->status === 'done')
                                                text-emerald-400
                                            @elseif($job->status === 'error')
                                                text-red-400
                                            @elseif($job->status === 'running')
                                                text-sky-400
                                            @else
                                                text-amber-400
                                            @endif
                                            font-black
                                        ">
                                            {{ strtoupper($job->status) }}
                                        </span>
                                    </td>

                                    <td class="p-3 message text-zinc-400">
                                        {{ $job->message }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center text-xs text-zinc-500">
                    Select a recent batch to view its jobs.
                </div>
            @endif
        </div>
    </section>

</main>

<script>
(() => {
    const input = document.getElementById('clothingFiles');
    const zone = document.getElementById('dropZone');
    const browse = document.getElementById('browseButton');
    const list = document.getElementById('fileList');
    const rows = document.getElementById('fileRows');
    const count = document.getElementById('fileCount');

    browse.addEventListener('click', () => input.click());

    zone.addEventListener('click', event => {
        if (event.target === browse) return;
        input.click();
    });

    for (const eventName of ['dragenter', 'dragover']) {
        zone.addEventListener(eventName, event => {
            event.preventDefault();
            event.stopPropagation();
            zone.classList.add('drop-active');
        });
    }

    for (const eventName of ['dragleave', 'drop']) {
        zone.addEventListener(eventName, event => {
            event.preventDefault();
            event.stopPropagation();
            zone.classList.remove('drop-active');
        });
    }

    zone.addEventListener('drop', event => {
        const transfer = new DataTransfer();

        for (const file of event.dataTransfer.files) {
            if (
                file.name.toLowerCase().endsWith('.swf') ||
                file.name.toLowerCase().endsWith('.nitro')
            ) {
                transfer.items.add(file);
            }
        }

        input.files = transfer.files;
        renderFiles();
    });

    input.addEventListener('change', renderFiles);

    function renderFiles() {
        rows.innerHTML = '';

        const files = Array.from(input.files);

        count.textContent =
            files.length + (files.length === 1 ? ' file' : ' files');

        list.classList.toggle('hidden', files.length === 0);

        for (const file of files) {
            const row = document.createElement('div');

            row.className =
                'px-4 py-3 flex items-center justify-between gap-4';

            const size =
                (file.size / 1024 / 1024).toFixed(2) + ' MB';

            row.innerHTML = `
                <div class="min-w-0">
                    <div class="text-xs font-bold text-zinc-200 truncate">
                        ${escapeHtml(file.name)}
                    </div>

                    <div class="text-[10px] text-zinc-500 mt-1">
                        ${size}
                    </div>
                </div>

                <span class="text-[10px] font-black uppercase text-amber-400">
                    ${file.name.toLowerCase().endsWith('.swf') ? 'SWF' : 'Nitro'}
                </span>
            `;

            rows.appendChild(row);
        }
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value;
        return div.innerHTML;
    }
})();
</script>

@if($selectedBatch)
<script>
(() => {
    const statusUrl =
        @json(route(
            'housekeeping.clothing-uploader.batch',
            ['batch' => $selectedBatch->id]
        ));

    async function refresh() {
        try {
            const response = await fetch(
                statusUrl,
                {
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            if (!response.ok) return;

            const payload = await response.json();

            let unfinished = false;

            for (const job of payload.jobs || []) {
                const row =
                    document.querySelector(
                        `[data-job="${job.id}"]`
                    );

                if (!row) continue;

                row.querySelector('.library').textContent =
                    job.library_name || '—';

                row.querySelector('.figure-type').textContent =
                    job.figure_type || '—';

                row.querySelector('.gender').textContent =
                    job.gender || '—';

                row.querySelector('.set-id').textContent =
                    job.set_id || '—';

                row.querySelector('.message').textContent =
                    job.message || '';

                const status =
                    row.querySelector('.status');

                status.innerHTML =
                    `<span class="font-black ${
                        job.status === 'done'
                            ? 'text-emerald-400'
                            : job.status === 'error'
                            ? 'text-red-400'
                            : job.status === 'running'
                            ? 'text-sky-400'
                            : 'text-amber-400'
                    }">${String(job.status).toUpperCase()}</span>`;

                if (
                    job.status === 'waiting' ||
                    job.status === 'running'
                ) {
                    unfinished = true;
                }
            }

            if (unfinished) {
                setTimeout(refresh, 3000);
            }
        } catch (_) {
            setTimeout(refresh, 5000);
        }
    }

    setTimeout(refresh, 1500);
})();
</script>
@endif

</body>
</html>
