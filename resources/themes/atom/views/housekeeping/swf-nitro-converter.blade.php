<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lounge ASE - SWF → Nitro Converter</title>

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

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        body {
            font-family:'Plus Jakarta Sans',sans-serif;
            background:#0f0507
                url('https://loungehotel.org/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png')
                no-repeat center center fixed;
            background-size:cover;
        }

        .drop-active {
            border-color:#facc15 !important;
            background:rgba(120,53,15,.35) !important;
        }
    </style>
</head>

<body class="min-h-screen text-white relative antialiased">

<div class="fixed inset-0 bg-black/75 backdrop-blur-[2px] z-0"></div>

<div class="relative z-10">

<header class="w-full bg-zinc-950/90 border-b border-amber-500/30 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">

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
                href="{{ route('housekeeping.furniture-uploader') }}"
                class="px-3 py-1.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-amber-500/30 text-amber-300 text-xs font-bold"
            >
                Furniture Uploader
            </a>

            <a
                href="{{ url('/housekeeping') }}"
                class="px-3 py-1.5 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 text-xs font-bold"
            >
                ← Dashboard
            </a>
        </div>

    </div>
</header>


<main class="max-w-6xl mx-auto px-4 py-8 space-y-6">

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-950/90 border border-emerald-500/40 text-emerald-300 font-bold text-xs">
            {{ session('success') }}
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

        <div class="border-b border-amber-500/20 pb-5 mb-6">
            <h1 class="text-2xl font-extrabold text-amber-400">
                SWF → Nitro Converter
            </h1>

            <p class="text-xs text-zinc-400 mt-2 max-w-3xl">
                Convert a single Habbo furniture SWF or an entire batch into
                Nitro bundles without installing the furniture into the hotel.
                Converted bundles are kept in an isolated temporary job and
                can be downloaded individually or together.
            </p>
        </div>


        <form
            method="POST"
            action="{{ route('housekeeping.swf-nitro-converter.convert') }}"
            enctype="multipart/form-data"
            id="converterForm"
        >
            @csrf

            <div
                id="swfDrop"
                class="rounded-3xl border-2 border-dashed border-amber-500/30 bg-black/40 p-12 text-center cursor-pointer transition"
            >
                <input
                    id="swfs"
                    name="swfs[]"
                    type="file"
                    accept=".swf,application/x-shockwave-flash"
                    multiple
                    required
                    class="hidden"
                >

                <div class="text-4xl mb-3">⇄</div>

                <div class="font-extrabold text-amber-300 text-lg">
                    Drop SWF files here
                </div>

                <div class="text-xs text-zinc-500 mt-2">
                    or click to browse • up to 100 SWFs per batch
                </div>
            </div>


            <div
                id="selection"
                class="hidden mt-5 rounded-2xl bg-black/40 border border-amber-500/20 overflow-hidden"
            >
                <div class="px-4 py-3 border-b border-amber-500/20 flex items-center justify-between">
                    <span class="text-xs font-extrabold text-amber-300 uppercase">
                        Selected SWFs
                    </span>

                    <span id="fileCount" class="text-[10px] text-zinc-500"></span>
                </div>

                <div id="fileRows" class="divide-y divide-zinc-800 max-h-64 overflow-auto"></div>
            </div>


            <div class="flex justify-end mt-6">
                <button
                    id="convertButton"
                    type="submit"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 font-extrabold text-amber-100 text-xs uppercase tracking-wider shadow-lg"
                >
                    Convert to Nitro
                </button>
            </div>

        </form>

    </section>


    @if($job && count($files))

        <section class="bg-zinc-900/95 border border-emerald-500/30 rounded-3xl p-6 shadow-xl">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">

                <div>
                    <h2 class="font-extrabold text-emerald-400">
                        Conversion Complete
                    </h2>

                    <p class="text-[10px] text-zinc-500 font-mono mt-1">
                        Job {{ $job }}
                    </p>
                </div>

                <a
                    href="{{ route('housekeeping.swf-nitro-converter.download-all', ['job'=>$job]) }}"
                    class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-emerald-800 hover:bg-emerald-700 border border-emerald-500/40 text-white text-xs font-extrabold"
                >
                    Download All
                    @if(count($files) > 1)
                        (.zip)
                    @endif
                </a>

            </div>


            <div class="space-y-2">

                @foreach($files as $file)

                    <div class="rounded-2xl bg-black/40 border border-zinc-800 p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">

                        <div class="min-w-0">
                            <div class="font-bold text-white font-mono text-xs truncate">
                                {{ $file['name'] }}
                            </div>

                            <div class="text-[10px] text-zinc-500 mt-1">
                                {{ number_format($file['size'] / 1024, 1) }} KB
                            </div>
                        </div>

                        <a
                            href="{{ route('housekeeping.swf-nitro-converter.download', ['job'=>$job,'file'=>$file['name']]) }}"
                            class="shrink-0 px-4 py-2 rounded-xl bg-red-950 hover:bg-red-900 border border-amber-500/30 text-amber-300 text-xs font-bold"
                        >
                            Download .nitro
                        </a>

                    </div>

                @endforeach

            </div>

        </section>

    @endif


    @if($job && $log)

        <details class="bg-zinc-900/90 border border-zinc-700 rounded-2xl p-5">

            <summary class="cursor-pointer text-xs font-extrabold text-zinc-300">
                Conversion log
            </summary>

            <pre class="mt-4 p-4 bg-black/70 rounded-xl overflow-auto max-h-80 text-[10px] text-zinc-400 whitespace-pre-wrap">{{ $log }}</pre>

        </details>

    @endif

</main>
</div>


<script>
const input = document.getElementById('swfs');
const drop = document.getElementById('swfDrop');
const selection = document.getElementById('selection');
const rows = document.getElementById('fileRows');
const count = document.getElementById('fileCount');
const form = document.getElementById('converterForm');
const button = document.getElementById('convertButton');

drop.addEventListener('click', () => input.click());

['dragenter','dragover'].forEach(eventName => {
    drop.addEventListener(eventName, event => {
        event.preventDefault();
        drop.classList.add('drop-active');
    });
});

['dragleave','drop'].forEach(eventName => {
    drop.addEventListener(eventName, event => {
        event.preventDefault();
        drop.classList.remove('drop-active');
    });
});

drop.addEventListener('drop', event => {
    input.files = event.dataTransfer.files;
    render();
});

input.addEventListener('change', render);

function escapeHtml(value) {
    return String(value).replace(
        /[&<>'"]/g,
        character => ({
            '&':'&amp;',
            '<':'&lt;',
            '>':'&gt;',
            "'":'&#39;',
            '"':'&quot;'
        }[character])
    );
}

function prettySize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';

    return (bytes / 1048576).toFixed(1) + ' MB';
}

function render() {
    const files = [...input.files];

    rows.innerHTML = '';

    files.forEach(file => {
        rows.insertAdjacentHTML(
            'beforeend',
            `
            <div class="px-4 py-3 flex items-center justify-between gap-4">
                <span class="font-mono text-xs text-zinc-200 truncate">
                    ${escapeHtml(file.name)}
                </span>
                <span class="text-[10px] text-zinc-500 shrink-0">
                    ${prettySize(file.size)}
                </span>
            </div>
            `
        );
    });

    count.textContent =
        files.length +
        ' file' +
        (files.length === 1 ? '' : 's');

    selection.classList.toggle('hidden', files.length === 0);
}

form.addEventListener('submit', () => {
    button.disabled = true;
    button.textContent = 'Converting...';
    button.classList.add('opacity-60', 'cursor-wait');
});
</script>

</body>
</html>
