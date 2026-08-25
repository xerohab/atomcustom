<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lounge - Staff Applications</title>

    <!-- Tailwind CSS CDN -->
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
                            600: '#ca8a04',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #0f0507 url('https://loungehotel.org/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png') no-repeat center center fixed;
            background-size: cover;
        }

        .lounge-card-bg {
            background-image: linear-gradient(to bottom, rgba(15, 5, 7, 0.85), rgba(15, 5, 7, 0.95)),
                              url('https://loungehotel.org/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body class="min-h-screen text-white relative overflow-x-hidden antialiased flex flex-col justify-between">

    <!-- Dark Page Overlay -->
    <div class="fixed inset-0 bg-black/60 backdrop-blur-[2px] z-0"></div>

    <!-- Content Wrapper -->
    <div class="relative z-10">

        <!-- UNIVERSAL NAVIGATION MENU -->
        @include('components.navigation.navigation-menu')

        <!-- MAIN CONTAINER -->
        <main class="max-w-7xl mx-auto px-4 py-8 space-y-6">

            <!-- HEADER HERO -->
            <div class="bg-zinc-900/90 backdrop-blur-md border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 shadow-2xl flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-extrabold text-amber-400">Staff Applications</h1>
                    <p class="text-xs text-zinc-300 mt-1 max-w-xl">
                        Want to help moderate, host events, or build for Lounge Hotel? Review the open staff positions below and submit an application.
                    </p>
                </div>
            </div>

            <!-- POSITIONS GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($positions ?? [] as $position)
                    <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 shadow-xl flex flex-col justify-between space-y-4 hover:border-amber-400/50 transition">

                        <div class="space-y-3">
                            <div class="flex items-center justify-between border-b border-amber-500/20 pb-3">
                                <div class="flex items-center gap-3">
                                    @if(!empty($position->rank->badge))
                                        <div class="h-10 w-10 rounded-xl bg-black/60 border border-amber-500/40 p-1 flex items-center justify-center flex-shrink-0">
                                            <img src="/gamedata/c_images/album1584/{{ $position->rank->badge }}.gif" alt="Badge" class="max-h-full max-w-full">
                                        </div>
                                    @endif
                                    <h2 class="text-base font-extrabold text-amber-400 uppercase tracking-wide">
                                        {{ $position->title ?? $position->rank->rank_name ?? 'Position' }}
                                    </h2>
                                </div>

                                <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full border {{ ($position->open ?? true) ? 'bg-emerald-950/80 border-emerald-500/40 text-emerald-400' : 'bg-red-950/80 border-red-500/40 text-red-400' }}">
                                    {{ ($position->open ?? true) ? 'Open' : 'Closed' }}
                                </span>
                            </div>

                            <p class="text-xs text-zinc-300 leading-relaxed">
                                {{ $position->description }}
                            </p>
                        </div>

                        <div class="pt-2">
                            @if($position->open ?? true)
                                <a href="{{ route('staff-applications.show', $position->id ?? $position->slug) }}" class="block w-full">
                                    <button type="button" class="w-full py-3 rounded-xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 font-bold text-amber-100 text-xs uppercase tracking-wider shadow-lg transition">
                                        Apply Now &rarr;
                                    </button>
                                </a>
                            @else
                                <button type="button" disabled class="w-full py-3 rounded-xl bg-zinc-800 border border-zinc-700 text-zinc-500 font-bold text-xs uppercase tracking-wider cursor-not-allowed">
                                    Applications Closed
                                </button>
                            @endif
                        </div>

                    </div>
                @empty
                    <div class="col-span-full bg-zinc-900/90 border border-amber-500/30 rounded-3xl p-8 text-center text-xs text-zinc-400 italic">
                        There are currently no open staff applications. Check back soon!
                    </div>
                @endforelse
            </div>

        </main>
    </div>

</body>
</html>