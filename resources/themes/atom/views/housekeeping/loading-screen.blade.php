<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lounge ASE - Loading Screen</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { gold: { 300: '#fde047', 400: '#facc15', 500: '#eab308', 600: '#ca8a04' } } } } }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0f0507 url('https://loungehotel.org/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png') no-repeat center center fixed; background-size: cover; }
        input[type="color"] { padding: 0.2rem; min-height: 42px; }
    </style>
</head>
<body class="min-h-screen text-white relative antialiased flex flex-col justify-between">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-[2px] z-0"></div>
    <div class="relative z-10">
        <header class="w-full bg-zinc-950/90 backdrop-blur-md border-b border-amber-500/30 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
                <a href="{{ url('/housekeeping') }}" class="flex items-center gap-2 min-w-0">
                    <img src="https://loungehotel.org/assets/images/lounge.png" alt="Lounge Logo" class="h-8 w-auto">
                    <span class="text-xs font-black uppercase tracking-widest text-amber-400 bg-red-950 border border-amber-500/40 px-2.5 py-1 rounded-lg">ASE</span>
                </a>
                <a href="{{ url('/housekeeping') }}" class="shrink-0 px-3 py-1.5 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 text-xs font-bold transition">&larr; Dashboard</a>
            </div>
        </header>

        <main class="max-w-6xl mx-auto px-4 py-8 space-y-6">
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-950 border border-emerald-500/40 text-emerald-300 font-bold text-xs">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-2xl bg-red-950 border border-red-500/40 text-red-200 text-xs space-y-1">
                    <div class="font-extrabold">Please correct the following:</div>
                    @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif

            <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-5 lg:p-8 shadow-xl space-y-6">
                <div class="border-b border-amber-500/20 pb-4 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-3">
                    <div>
                        <h1 class="text-xl font-extrabold text-amber-400 uppercase tracking-wide">Client Loading Screen</h1>
                        <p class="text-xs text-zinc-400 mt-1">Control Nitro's loading artwork, headings, colours and tips without rebuilding the client.</p>
                    </div>
                    <a href="{{ $endpointUrl }}" target="_blank" rel="noopener" class="text-xs font-bold text-amber-300 hover:text-amber-200 break-all">View live JSON endpoint &rarr;</a>
                </div>

                <form action="{{ url('/housekeeping/loading-screen') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <section class="p-5 rounded-2xl bg-black/40 border border-amber-500/20 space-y-4">
                            <h2 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider">General</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                <label class="space-y-1.5">
                                    <span class="block font-bold text-amber-300">Custom Loading Screen</span>
                                    <select name="enabled" class="w-full px-4 py-2.5 rounded-xl bg-black/60 border border-amber-500/30 text-white">
                                        <option value="1" {{ old('enabled', $config['enabled'] ? '1' : '0') === '1' ? 'selected' : '' }}>Enabled</option>
                                        <option value="0" {{ old('enabled', $config['enabled'] ? '1' : '0') === '0' ? 'selected' : '' }}>Disabled</option>
                                    </select>
                                </label>

                                <label class="space-y-1.5">
                                    <span class="block font-bold text-amber-300">Panel Opacity</span>
                                    <input type="number" step="0.01" min="0.35" max="0.95" name="panel_opacity" value="{{ old('panel_opacity', $config['panel_opacity']) }}" class="w-full px-4 py-2.5 rounded-xl bg-black/60 border border-amber-500/30 text-white">
                                </label>
                            </div>

                            <label class="block space-y-1.5 text-xs">
                                <span class="block font-bold text-amber-300">Background Image URL</span>
                                <input type="url" name="background_url" value="{{ old('background_url', $config['background_url']) }}" class="w-full px-4 py-2.5 rounded-xl bg-black/60 border border-amber-500/30 text-white">
                            </label>

                            <label class="block space-y-1.5 text-xs">
                                <span class="block font-bold text-amber-300">Logo Image URL</span>
                                <input type="url" name="logo_url" value="{{ old('logo_url', $config['logo_url']) }}" class="w-full px-4 py-2.5 rounded-xl bg-black/60 border border-amber-500/30 text-white">
                            </label>
                        </section>

                        <section class="p-5 rounded-2xl bg-black/40 border border-amber-500/20 space-y-4">
                            <h2 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider">Progress Bar</h2>
                            <p class="text-[11px] text-zinc-500">Choose the three colours used across the gold progress gradient.</p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                                @foreach(['start' => 'Start', 'middle' => 'Middle', 'end' => 'End'] as $key => $label)
                                    <label class="space-y-1.5">
                                        <span class="block font-bold text-amber-300">{{ $label }}</span>
                                        <div class="flex gap-2">
                                            <input type="color" data-color-picker="progress_{{ $key }}" value="{{ old('progress_'.$key, $config['progress_colors'][$key]) }}" class="w-12 rounded-lg bg-black/60 border border-amber-500/30">
                                            <input type="text" id="progress_{{ $key }}" name="progress_{{ $key }}" value="{{ old('progress_'.$key, $config['progress_colors'][$key]) }}" class="min-w-0 flex-1 px-3 py-2.5 rounded-xl bg-black/60 border border-amber-500/30 text-white uppercase">
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <div class="h-5 rounded-full border border-amber-500/30 overflow-hidden bg-black" id="gradient-preview"></div>
                        </section>
                    </div>

                    <section class="p-5 rounded-2xl bg-black/40 border border-amber-500/20 space-y-4">
                        <h2 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider">Progress Headings</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                            @foreach([
                                'preparing' => ['Preparing / Early Loading', 'heading_preparing'],
                                'loading' => ['Normal Loading', 'heading_loading'],
                                'almost' => ['Almost Complete', 'heading_almost'],
                                'complete' => ['100% Complete', 'heading_complete'],
                            ] as $key => [$label, $name])
                                <label class="space-y-1.5">
                                    <span class="block font-bold text-amber-300">{{ $label }}</span>
                                    <input type="text" name="{{ $name }}" value="{{ old($name, $config['headings'][$key]) }}" maxlength="80" class="w-full px-4 py-2.5 rounded-xl bg-black/60 border border-amber-500/30 text-white">
                                </label>
                            @endforeach
                        </div>
                    </section>

                    <section class="p-5 rounded-2xl bg-black/40 border border-amber-500/20 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                            <div>
                                <h2 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider">Loading Tips</h2>
                                <p class="text-[11px] text-zinc-500 mt-1">One tip per line. Empty lines are ignored. Maximum 100 tips.</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3 text-xs w-full sm:w-auto">
                                <label class="space-y-1.5">
                                    <span class="block font-bold text-amber-300">Tips</span>
                                    <select name="tips_enabled" class="w-full px-4 py-2.5 rounded-xl bg-black/60 border border-amber-500/30 text-white">
                                        <option value="1" {{ old('tips_enabled', $config['tips_enabled'] ? '1' : '0') === '1' ? 'selected' : '' }}>Enabled</option>
                                        <option value="0" {{ old('tips_enabled', $config['tips_enabled'] ? '1' : '0') === '0' ? 'selected' : '' }}>Disabled</option>
                                    </select>
                                </label>
                                <label class="space-y-1.5">
                                    <span class="block font-bold text-amber-300">Change Every</span>
                                    <div class="flex items-center gap-2">
                                        <input type="number" name="rotation_seconds" min="2" max="30" value="{{ old('rotation_seconds', $config['rotation_seconds']) }}" class="w-20 px-3 py-2.5 rounded-xl bg-black/60 border border-amber-500/30 text-white">
                                        <span class="text-zinc-500">sec</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <textarea name="tips_text" rows="12" class="w-full px-4 py-3 rounded-xl bg-black/60 border border-amber-500/30 text-white text-xs leading-6 focus:outline-none focus:ring-2 focus:ring-amber-500">{{ old('tips_text', implode("\n", $config['tips'])) }}</textarea>
                    </section>

                    <div class="pt-2 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                        <p class="text-[11px] text-zinc-500">Changes are served from Atom. Once Nitro is connected in the final step, no rebuild will be needed for future edits.</p>
                        <button type="submit" class="shrink-0 px-8 py-3 rounded-xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 font-bold text-amber-100 text-xs uppercase tracking-wider transition shadow-lg">Save Loading Screen</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        (() => {
            const updatePreview = () => {
                const start = document.getElementById('progress_start')?.value || '#8A4D00';
                const middle = document.getElementById('progress_middle')?.value || '#EBA915';
                const end = document.getElementById('progress_end')?.value || '#FFD85E';
                const preview = document.getElementById('gradient-preview');
                if (preview) preview.style.background = `linear-gradient(90deg, ${start}, ${middle}, ${end})`;
            };

            document.querySelectorAll('[data-color-picker]').forEach((picker) => {
                picker.addEventListener('input', () => {
                    const target = document.getElementById(picker.dataset.colorPicker);
                    if (target) target.value = picker.value.toUpperCase();
                    updatePreview();
                });
            });

            ['progress_start', 'progress_middle', 'progress_end'].forEach((id) => {
                document.getElementById(id)?.addEventListener('input', updatePreview);
            });

            updatePreview();
        })();
    </script>
</body>
</html>
