<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lounge - {{ $user->username }}'s Profile</title>

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #0f0507 url('{{ !empty($user->profile_background) ? asset('storage/' . $user->profile_background) : 'https://loungehotel.org/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png' }}') no-repeat center center fixed;
            background-size: cover;
        }

        .lounge-card-bg {
            background-image: linear-gradient(to bottom, rgba(15, 5, 7, 0.85), rgba(15, 5, 7, 0.95)),
                              url('{{ !empty($user->profile_background) ? asset('storage/' . $user->profile_background) : 'https://loungehotel.org/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png' }}');
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
        <main class="max-w-6xl mx-auto px-4 py-8 space-y-6">

            <!-- PROFILE HEADER HERO -->
            <div class="bg-zinc-900/80 backdrop-blur-md border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 shadow-2xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-8">

                <!-- LEFT SIDE: USER INFO -->
                <div class="flex items-start gap-6 relative z-10 min-w-0 flex-1">
                    <!-- Avatar Frame -->
                    <div class="h-28 w-24 rounded-2xl bg-zinc-950/80 border border-amber-500/40 overflow-hidden flex-shrink-0 relative flex items-center justify-center shadow-inner mt-1">
                        <img src="{{ setting('avatar_imager') }}{{ $user->look }}&direction=2&head_direction=2&gesture=sml&action=wav&size=l"
                             alt="{{ $user->username }}" class="-mt-2 scale-110" style="image-rendering: pixelated;">
                    </div>

                    <!-- User Information -->
                    <div class="space-y-1.5 min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-2xl lg:text-3xl font-extrabold text-amber-400">{{ $user->username }}</h1>
                            <span class="h-2.5 w-2.5 rounded-full flex-shrink-0 {{ $user->online ? 'bg-emerald-400 animate-pulse' : 'bg-zinc-600' }}"
                                  title="{{ $user->online ? 'Online' : 'Offline' }}"></span>

                            <!-- DISCORD VERIFIED BADGE -->
                            @if(!empty($user->discord_id))
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-indigo-500/20 border border-indigo-500/40 text-indigo-300 text-[10px] font-extrabold uppercase tracking-wider" title="Discord Verified">
                                    <svg class="w-3 h-3 fill-current text-indigo-400" viewBox="0 0 127.14 96.36">
                                        <path d="M107.7,8.07A105.15,105.15,0,0,0,81.47,0a72.06,72.06,0,0,0-3.36,6.83A97.68,97.68,0,0,0,49,6.83,72.37,72.37,0,0,0,45.64,0,105.89,105.89,0,0,0,19.39,8.09C2.79,32.65-1.71,56.6.54,80.21h0A105.73,105.73,0,0,0,32.71,96.36,77.7,77.7,0,0,0,39.6,85.25a68.42,68.42,0,0,1-10.85-5.18c.91-.66,1.8-1.34,2.66-2a75.57,75.57,0,0,0,64.32,0c.87.71,1.76,1.39,2.66,2a68.68,68.68,0,0,1-10.87,5.19,77,77,0,0,0,6.89,11.1A105.25,105.25,0,0,0,126.6,80.22h0C129.24,52.84,122.09,29.11,107.7,8.07ZM42.45,65.69C36.18,65.69,31,60,31,53s5-12.74,11.43-12.74S54,45.92,53.86,53,48.83,65.69,42.45,65.69Zm42.24,0C78.41,65.69,73.25,60,73.25,53s5-12.74,11.44-12.74S96.23,45.92,96.09,53,91.08,65.69,84.69,65.69Z"/>
                                    </svg>
                                    <span>Verified</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-zinc-800/80 border border-zinc-700 text-zinc-400 text-[10px] font-bold uppercase tracking-wider" title="Not Verified with Discord">
                                    <svg class="w-3 h-3 fill-current text-zinc-500" viewBox="0 0 127.14 96.36">
                                        <path d="M107.7,8.07A105.15,105.15,0,0,0,81.47,0a72.06,72.06,0,0,0-3.36,6.83A97.68,97.68,0,0,0,49,6.83,72.37,72.37,0,0,0,45.64,0,105.89,105.89,0,0,0,19.39,8.09C2.79,32.65-1.71,56.6.54,80.21h0A105.73,105.73,0,0,0,32.71,96.36,77.7,77.7,0,0,0,39.6,85.25a68.42,68.42,0,0,1-10.85-5.18c.91-.66,1.8-1.34,2.66-2a75.57,75.57,0,0,0,64.32,0c.87.71,1.76,1.39,2.66,2a68.68,68.68,0,0,1-10.87,5.19,77,77,0,0,0,6.89,11.1A105.25,105.25,0,0,0,126.6,80.22h0C129.24,52.84,122.09,29.11,107.7,8.07ZM42.45,65.69C36.18,65.69,31,60,31,53s5-12.74,11.43-12.74S54,45.92,53.86,53,48.83,65.69,42.45,65.69Zm42.24,0C78.41,65.69,73.25,60,73.25,53s5-12.74,11.44-12.74S96.23,45.92,96.09,53,91.08,65.69,84.69,65.69Z"/>
                                    </svg>
                                    <span>Unverified</span>
                                </span>
                            @endif
                        </div>

                        <p class="text-xs text-zinc-300 italic truncate">"{{ $user->motto ?? 'No motto set' }}"</p>

                        <!-- ABOUT ME / UNIQUE QUOTE BOX -->
                        @if(!empty($user->profile_quote))
                            <div class="mt-2 p-3 rounded-2xl bg-black/50 border border-amber-500/20 max-w-full shadow-inner space-y-1">
                                <div class="flex items-center gap-1.5 border-b border-amber-500/10 pb-1">
                                    <span class="px-2 py-0.5 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-[9px] font-extrabold uppercase tracking-widest">
                                        About Me / Unique Quote
                                    </span>
                                </div>

                                @if(!empty($user->profile_quote_prefix))
                                    <span class="block text-[10px] font-extrabold text-amber-400 uppercase tracking-wider pt-1">
                                        {{ $user->profile_quote_prefix }}
                                    </span>
                                @endif

                                <p class="text-xs text-zinc-200 leading-relaxed font-medium line-clamp-3 whitespace-pre-line">
                                    {{ $user->profile_quote }}
                                </p>
                            </div>
                        @endif

                        <div class="pt-2 text-[11px] text-zinc-400 flex flex-wrap gap-x-4 gap-y-1 font-bold">
                            <span>Joined: <strong class="text-amber-200">{{ $user->account_created > 0 ? date('M Y', $user->account_created) : date('M Y', strtotime($user->created_at ?? 'now')) }}</strong></span>
                            <span>Last Online: <strong class="text-amber-200">{{ date('d M Y, H:i', $user->last_online ?? strtotime($user->updated_at ?? 'now')) }}</strong></span>
                        </div>

                        <!-- EDIT PROFILE LINK (OWN PROFILE ONLY) -->
                        @auth
                            @if(auth()->id() === $user->id)
                                <div class="pt-2">
                                    <a href="{{ url('/user/settings/account') }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-amber-500/10 border border-amber-500/40 text-amber-300 hover:bg-amber-500 hover:text-black font-extrabold text-[11px] uppercase tracking-wider transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        <span>Edit Profile</span>
                                    </a>
                                </div>
                            @endif
                        @endauth
                    </div>
                </div>

                <!-- RIGHT SIDE: BANNER BOX WITH NO IMAGE CROPPING -->
                <div class="w-full md:w-[420px] lg:w-[460px] h-56 rounded-2xl overflow-hidden border border-amber-500/30 bg-black/60 flex-shrink-0 relative shadow-inner group flex items-center justify-center p-2">

                    <img id="bannerPreview"
                         src="{{ !empty($user->profile_banner) ? asset('storage/' . $user->profile_banner) : '' }}"
                         alt="{{ $user->username }}'s Banner"
                         class="max-w-full max-h-full object-contain {{ empty($user->profile_banner) ? 'hidden' : '' }}">

                    <div id="noBannerPlaceholder" class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-r from-amber-500/5 to-amber-500/10 text-amber-500/40 p-4 text-center {{ !empty($user->profile_banner) ? 'hidden' : '' }}">
                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-[11px] font-bold uppercase tracking-wider">No Custom Banner</span>
                    </div>

                    <!-- BANNER EDIT & SAVE OVERLAY CONTROLS (OWN PROFILE ONLY) -->
                    @auth
                        @if(auth()->id() === $user->id)
                            <form id="bannerForm" action="{{ url('/user/settings/account') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <input type="file" id="bannerFileInput" name="profile_banner" accept="image/*" class="hidden" onchange="previewBanner(event)">
                                <input type="hidden" name="motto" value="{{ auth()->user()->motto }}">
                                <input type="hidden" name="mail" value="{{ auth()->user()->mail }}">

                                <button type="button" onclick="document.getElementById('bannerFileInput').click()"
                                        class="absolute bottom-3 left-3 bg-black/80 hover:bg-black border border-amber-500/40 text-amber-300 px-3 py-1.5 rounded-xl text-[10px] font-extrabold uppercase tracking-wider backdrop-blur-md opacity-0 group-hover:opacity-100 transition shadow-lg flex items-center gap-1.5 z-20">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    </svg>
                                    <span>Change Banner</span>
                                </button>

                                <button type="submit" id="saveBannerBtn"
                                        class="hidden absolute bottom-3 right-3 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 border border-emerald-300/40 text-white px-4 py-1.5 rounded-xl text-[10px] font-extrabold uppercase tracking-wider shadow-xl transition animate-bounce z-20">
                                    Save Banner
                                </button>
                            </form>
                        @endif
                    @endauth

                </div>

            </div>

            <!-- PROFILE STATS ROW -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-zinc-900/80 border border-amber-500/30 rounded-2xl p-4 text-center backdrop-blur-md">
                    <span class="block text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Achievement</span>
                    <span class="text-base font-extrabold text-amber-400">
                        {{ number_format($user->settings->achievement_score ?? 0) }}
                    </span>
                </div>
                <div class="bg-zinc-900/80 border border-amber-500/30 rounded-2xl p-4 text-center backdrop-blur-md">
                    <span class="block text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Total Respects</span>
                    <span class="text-base font-extrabold text-amber-400">
                        {{ number_format($user->settings->respects_received ?? 0) }}
                    </span>
                </div>
                <div class="bg-zinc-900/80 border border-amber-500/30 rounded-2xl p-4 text-center backdrop-blur-md">
                    <span class="block text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Total Online Time</span>
                    <span class="text-base font-extrabold text-amber-400">
                        {{ round(($user->settings->online_time ?? 0) / 3600) }} hrs
                    </span>
                </div>
                <div class="bg-zinc-900/80 border border-amber-500/30 rounded-2xl p-4 text-center backdrop-blur-md">
                    <span class="block text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Rank</span>
                    <span class="text-base font-extrabold text-amber-400">{{ $user->permission->rank_name ?? 'Player' }}</span>
                </div>

                <!-- DISCORD STATS TILE -->
                <div class="col-span-2 md:col-span-1 bg-zinc-900/80 border border-amber-500/30 rounded-2xl p-4 text-center backdrop-blur-md">
                    <span class="block text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Discord</span>
                    @if(!empty($user->discord_id))
                        <span class="text-sm font-extrabold text-indigo-400 block mt-0.5">
                            Verified
                        </span>
                    @else
                        <span class="text-sm font-extrabold text-zinc-500 block mt-0.5">
                            Not Linked
                        </span>
                    @endif
                </div>
            </div>

            <!-- PROFILE MAIN CONTENT AREA -->
            <div class="space-y-6">

                <!-- BADGES CARD -->
                <div class="bg-zinc-900/80 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 shadow-xl space-y-4">
                    <h2 class="text-sm font-extrabold text-amber-400 uppercase tracking-wide border-b border-amber-500/20 pb-3">
                        Equipped Badges
                    </h2>

                    @php
                        $badges = collect($user->badges ?? [])->filter(fn($b) => ($b->slot_id ?? 0) > 0);
                        if ($badges->isEmpty()) {
                            $badges = collect($user->badges ?? []);
                        }
                    @endphp

                    <div class="flex flex-wrap gap-3">
                        @forelse($badges as $badge)
                            @if(!empty($badge->badge_code))
                                <div class="h-12 w-12 rounded-xl bg-black/60 border border-amber-500/30 p-1.5 flex items-center justify-center shadow-inner hover:border-amber-400 transition"
                                     title="{{ $badge->badge_code }}">
                                    <img src="/gamedata/c_images/album1584/{{ $badge->badge_code }}.gif"
                                         alt="{{ $badge->badge_code }}" class="max-h-full max-w-full">
                                </div>
                            @endif
                        @empty
                            <div class="text-xs text-zinc-500 italic py-2">
                                This user has no equipped badges.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- OWNED ROOMS CARD -->
                <div class="bg-zinc-900/80 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 shadow-xl space-y-4">
                    <div class="flex justify-between items-center border-b border-amber-500/20 pb-3">
                        <h2 class="text-sm font-extrabold text-amber-400 uppercase tracking-wide">
                            Rooms Owned ({{ count($user->rooms ?? []) }})
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @forelse($user->rooms ?? [] as $room)
                            <div class="p-3.5 rounded-2xl bg-black/50 border border-amber-500/20 flex items-center justify-between gap-3 hover:border-amber-500/40 transition">
                                <div class="space-y-1 min-w-0 flex-grow">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-amber-300 truncate">{{ $room->name }}</span>
                                        @if($room->state === 'locked')
                                            <span class="text-[10px] px-2 py-0.2 rounded bg-red-950/80 border border-red-500/40 text-red-300">Locked</span>
                                        @elseif($room->state === 'password')
                                            <span class="text-[10px] px-2 py-0.2 rounded bg-amber-950/80 border border-amber-500/40 text-amber-300">Password</span>
                                        @else
                                            <span class="text-[10px] px-2 py-0.2 rounded bg-emerald-950/80 border border-emerald-500/40 text-emerald-300">Open</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-zinc-400 truncate">{{ $room->description ?: 'No description provided.' }}</p>
                                </div>

                                <div class="text-right flex-shrink-0 bg-zinc-950 px-2.5 py-1 rounded-xl border border-amber-500/20">
                                    <span class="text-[10px] font-extrabold text-amber-400 block">{{ $room->users }}/{{ $room->users_max }}</span>
                                    <span class="text-[9px] uppercase font-bold text-zinc-500">Users</span>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2 text-center py-6 text-xs text-zinc-500 italic">
                                This user does not own any rooms yet.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- GUESTBOOK CARD -->
                <div class="bg-zinc-900/80 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 shadow-xl space-y-4">
                    <h2 class="text-sm font-extrabold text-amber-400 uppercase tracking-wide border-b border-amber-500/20 pb-3">
                        Guestbook
                    </h2>

                    <!-- Post Guestbook Message Form -->
                    @auth
                        <form action="{{ url('/profile/' . $user->username . '/guestbook') }}" method="POST" class="space-y-3">
                            @csrf
                            <textarea name="message" rows="3" required placeholder="Leave a message on {{ $user->username }}'s profile..."
                                      class="w-full p-3 rounded-xl bg-black/50 border border-amber-500/30 text-white placeholder-zinc-500 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500/50"></textarea>
                            <div class="flex justify-end">
                                <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 font-bold text-amber-100 text-xs uppercase tracking-wider transition">
                                    Post Comment
                                </button>
                            </div>
                        </form>
                    @endauth

                    <!-- Messages List -->
                    <div class="space-y-3 pt-2">
                        @forelse($user->guestbook ?? [] as $entry)
                            @php
                                $author = $entry->user ?? $entry->author ?? null;
                            @endphp
                            <div class="p-3.5 rounded-2xl bg-black/50 border border-amber-500/20 flex items-start gap-3">
                                <a href="{{ url('/profile/' . ($author->username ?? '')) }}" class="h-10 w-10 rounded-full border border-amber-500/30 bg-zinc-950 overflow-hidden flex-shrink-0 flex items-center justify-center hover:border-amber-400 transition">
                                    <img src="{{ setting('avatar_imager') }}{{ $author->look ?? '' }}&direction=2&headonly=1&head_direction=2&gesture=sml"
                                         alt="Avatar" class="w-full h-full object-cover">
                                </a>
                                <div class="flex-grow min-w-0 space-y-1">
                                    <div class="flex justify-between items-center">
                                        <a href="{{ url('/profile/' . ($author->username ?? '')) }}" class="text-xs font-bold text-amber-300 hover:text-amber-400 hover:underline">
                                            {{ $author->username ?? 'Unknown' }}
                                        </a>
                                        <span class="text-[10px] text-zinc-500">
                                            {{ date('d M Y, H:i', strtotime($entry->created_at ?? 'now')) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-zinc-300 leading-relaxed">
                                        {{ $entry->message }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 text-xs text-zinc-500 italic">
                                No guestbook messages yet. Be the first to leave a comment!
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

        </main>
    </div>

    <!-- SCRIPT FOR REAL-TIME BANNER PREVIEW -->
    <script>
        function previewBanner(event) {
            const file = event.target.files[0];
            if (file) {
                const img = document.getElementById('bannerPreview');
                const placeholder = document.getElementById('noBannerPlaceholder');
                const saveBtn = document.getElementById('saveBannerBtn');

                img.src = URL.createObjectURL(file);
                img.classList.remove('hidden');

                if (placeholder) {
                    placeholder.classList.add('hidden');
                }

                if (saveBtn) {
                    saveBtn.classList.remove('hidden');
                }
            }
        }
    </script>

</body>
</html>