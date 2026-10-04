<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solace - Hotel Photos</title>

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
            background: #0f0507 url('https://solacehotel.pw/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png') no-repeat center center fixed;
            background-size: cover;
        }

        .solace-card-bg {
            background-image: linear-gradient(to bottom, rgba(15, 5, 7, 0.85), rgba(15, 5, 7, 0.95)),
                              url('https://solacehotel.pw/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png');
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
                    <h1 class="text-3xl font-extrabold text-amber-400">Hotel Photos</h1>
                    <p class="text-xs text-zinc-300 mt-1 max-w-xl">
                        Check out the latest moments captured by players across Solace Hotel using the in-game camera.
                    </p>
                </div>
            </div>

            <!-- PHOTOS FEED GRID -->
            <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 shadow-xl space-y-4">
                <div class="flex justify-between items-center border-b border-amber-500/20 pb-3">
                    <h2 class="text-sm font-extrabold text-amber-400 uppercase tracking-wide">
                        Camera Feed
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @forelse($photos ?? [] as $photo)
                        <div class="rounded-2xl overflow-hidden border border-amber-500/20 bg-black/50 shadow-inner flex flex-col justify-between group hover:border-amber-400/60 transition duration-300">
                            <div class="h-48 overflow-hidden relative">
                                <img src="{{ $photo->url }}" alt="Snapshot" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            </div>
                            <div class="p-3 flex items-center gap-3 bg-zinc-950/90 border-t border-amber-500/20">
                                <div class="h-9 w-9 rounded-full border border-amber-500/30 bg-black overflow-hidden flex-shrink-0 flex items-center justify-center">
                                    <img src="{{ setting('avatar_imager') }}{{ $photo->user->look ?? '' }}&direction=2&headonly=1&head_direction=2&gesture=sml"
                                         alt="{{ $photo->user->username ?? 'User' }}" class="w-full h-full object-cover">
                                </div>
                                <div class="truncate">
                                    <span class="block text-xs font-bold text-amber-300 truncate">{{ $photo->user->username ?? 'Unknown' }}</span>
                                    <span class="block text-[10px] text-zinc-400">{{ date('d M Y', $photo->created_at?->timestamp ?? time()) }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-12 text-center py-12 text-xs text-zinc-400 italic">
                            No camera snapshots taken yet. Snap a photo in-game to show up here!
                        </div>
                    @endforelse
                </div>

                <!-- PAGINATION (IF AVAILABLE) -->
                @if(isset($photos) && method_exists($photos, 'links'))
                    <div class="pt-4 border-t border-amber-500/20">
                        {{ $photos->links() }}
                    </div>
                @endif
            </div>

        </main>
    </div>

</body>
</html>