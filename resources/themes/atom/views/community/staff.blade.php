<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solace - Hotel Staff</title>

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

            <div class="grid grid-cols-12 gap-6 items-start">

                <!-- LEFT SIDE: STAFF RANKS & CARDS (8 COLS) -->
                <div class="col-span-12 lg:col-span-8 space-y-6">
                    @forelse($employees ?? [] as $rank)
                        <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl overflow-hidden shadow-2xl">

                            <!-- RANK HEADER BANNER (Top Bar) -->
                            <div class="p-4 sm:p-5 flex items-center justify-between border-b border-amber-500/30 bg-gradient-to-r from-red-950/80 via-zinc-900/90 to-zinc-950/90 relative">
                                <div class="flex items-center gap-3.5">
                                    @if(!empty($rank->badge))
                                        <div class="h-12 w-12 rounded-xl bg-black/60 border border-amber-500/40 p-1 flex items-center justify-center flex-shrink-0 shadow-inner">
                                            <img src="/gamedata/c_images/album1584/{{ $rank->badge }}.gif"
                                                 alt="{{ $rank->rank_name }}" title="{{ $rank->rank_name }}" class="max-h-full max-w-full">
                                        </div>
                                    @endif
                                    <div>
                                        <h2 class="text-base font-extrabold uppercase tracking-wide text-amber-400">
                                            {{ $rank->rank_name }}
                                        </h2>
                                        <p class="text-xs text-zinc-300 line-clamp-1">
                                            {{ $rank->job_description }}
                                        </p>
                                    </div>
                                </div>

                                <span class="text-xs font-bold text-amber-200/80 bg-black/50 border border-amber-500/30 px-3 py-1.5 rounded-xl whitespace-nowrap">
                                    {{ count($rank->users) }} {{ Str::plural('member', count($rank->users)) }}
                                </span>
                            </div>

                            <!-- STAFF CARDS CONTAINER -->
                            <div class="p-4 sm:p-5 space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @forelse($rank->users as $staff)
                                        <div class="rounded-2xl bg-black/60 border border-amber-500/20 hover:border-amber-400/50 transition shadow-inner overflow-hidden flex flex-col justify-between p-3.5 group">

                                            <div class="flex items-start gap-3">
                                                <!-- Clickable Avatar & Info leading to User Profile -->
                                                <a href="{{ url('/profile/' . $staff->username) }}" class="flex items-start gap-3 flex-grow min-w-0">
                                                    <!-- Full Body Avatar Box -->
                                                    <div class="h-28 w-20 rounded-xl bg-zinc-950/80 border border-amber-500/30 overflow-hidden flex-shrink-0 relative flex items-center justify-center group-hover:border-amber-400 transition">
                                                        <img src="{{ setting('avatar_imager') }}{{ $staff->look }}&direction=2&head_direction=2&gesture=sml&action=wav&size=l"
                                                             alt="{{ $staff->username }}" class="-mt-2 scale-110" style="image-rendering: pixelated;">
                                                    </div>

                                                    <!-- Staff Details -->
                                                    <div class="space-y-1 min-w-0 flex-grow">
                                                        <div class="flex items-center gap-1.5">
                                                            <span class="text-sm font-extrabold text-white group-hover:text-amber-300 group-hover:underline transition truncate">{{ $staff->username }}</span>
                                                            <span class="h-2 w-2 rounded-full flex-shrink-0 {{ $staff->online ? 'bg-emerald-400 animate-pulse' : 'bg-zinc-600' }}"
                                                                  title="{{ $staff->online ? 'Online' : 'Offline' }}"></span>
                                                        </div>

                                                        <span class="block text-[11px] font-bold text-amber-400 truncate">
                                                            {{ $rank->rank_name }}
                                                        </span>

                                                        <p class="text-xs text-zinc-300 italic line-clamp-2 pt-0.5">
                                                            "{{ $staff->motto ?? 'No motto set' }}"
                                                        </p>

                                                        <div class="text-[10px] text-zinc-400 pt-1 space-y-0.5">
                                                            <span class="block">
                                                                Currently {{ $staff->online ? 'online' : 'offline' }}
                                                            </span>
                                                            <span class="block">
                                                                Joined {{ $staff->account_created > 0 ? date('M Y', $staff->account_created) : date('M Y', strtotime($staff->created_at ?? 'now')) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </a>

                                                <!-- Equipped User Badges (2x2 Grid) -->
                                                <div class="grid grid-cols-2 gap-1 flex-shrink-0">
                                                    @if(isset($staff->badges) && count($staff->badges) > 0)
                                                        @foreach($staff->badges->take(4) as $userBadge)
                                                            <div class="h-8 w-8 rounded-lg bg-zinc-950/80 border border-amber-500/20 p-0.5 flex items-center justify-center">
                                                                <img src="/gamedata/c_images/album1584/{{ $userBadge->badge_code }}.gif"
                                                                     alt="Badge" class="max-h-full max-w-full">
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="h-8 w-8 rounded-lg bg-zinc-950/40 border border-amber-500/10"></div>
                                                        <div class="h-8 w-8 rounded-lg bg-zinc-950/40 border border-amber-500/10"></div>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>
                                    @empty
                                        <div class="col-span-full text-xs text-zinc-500 italic py-2">
                                            No staff assigned to this rank.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                        </div>
                    @empty
                        <div class="bg-zinc-900/90 border border-amber-500/30 rounded-3xl p-8 text-center text-xs text-zinc-400 italic">
                            No staff members found.
                        </div>
                    @endforelse
                </div>

                <!-- RIGHT SIDE: ONLINE STAFF SIDEBAR (4 COLS) -->
                <div class="col-span-12 lg:col-span-4 space-y-6">

                    @php
                        $onlineStaff = collect($employees ?? [])->flatMap(function($rank) {
                            return $rank->users;
                        })->where('online', 1);
                    @endphp

                    <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 shadow-xl space-y-4">
                        <div class="flex justify-between items-center border-b border-amber-500/20 pb-3">
                            <h2 class="text-sm font-extrabold text-amber-400 uppercase tracking-wide flex items-center gap-2">
                                Online Staff
                            </h2>
                            <span class="text-xs font-bold text-amber-300 bg-black/50 border border-amber-500/30 px-2.5 py-1 rounded-xl">
                                {{ $onlineStaff->count() }} online
                            </span>
                        </div>

                        <div class="space-y-3">
                            @forelse($onlineStaff as $online)
                                <a href="{{ url('/profile/' . $online->username) }}" class="p-3 rounded-2xl bg-black/50 border border-amber-500/20 hover:border-amber-400/50 flex items-center gap-3 group transition">
                                    <div class="h-10 w-10 rounded-full border border-amber-500/40 bg-black overflow-hidden flex-shrink-0 flex items-center justify-center group-hover:border-amber-400 transition">
                                        <img src="{{ setting('avatar_imager') }}{{ $online->look }}&direction=2&headonly=1&head_direction=2&gesture=sml"
                                             alt="{{ $online->username }}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="truncate">
                                        <span class="block text-xs font-bold text-amber-300 group-hover:underline truncate">{{ $online->username }}</span>
                                        <span class="block text-[10px] text-zinc-400 truncate">{{ $online->motto ?? 'Online now' }}</span>
                                    </div>
                                </a>
                            @empty
                                <div class="text-center py-6 space-y-2">
                                    <div class="text-2xl">??</div>
                                    <p class="text-xs text-zinc-400 italic">No staff online right now.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>

            </div>

        </main>
    </div>

</body>
</html>