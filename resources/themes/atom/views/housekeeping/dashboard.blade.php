<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lounge ASE - Administration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { gold: { 300: '#fde047', 400: '#facc15', 500: '#eab308', 600: '#ca8a04' } } } } }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0f0507 url('https://loungehotel.org/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png') no-repeat center center fixed; background-size: cover; }
    </style>
</head>
<body class="min-h-screen text-white relative antialiased flex flex-col justify-between">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-[2px] z-0"></div>
    <div class="relative z-10">

        <!-- HEADER -->
        <header class="w-full bg-zinc-950/90 backdrop-blur-md border-b border-amber-500/30 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ url('/user/me') }}" class="flex items-center gap-2">
                        <img src="https://loungehotel.org/assets/images/lounge.png" alt="Lounge Logo" class="h-8 w-auto">
                        <span class="text-xs font-black uppercase tracking-widest text-amber-400 bg-red-950 border border-amber-500/40 px-2.5 py-1 rounded-lg">ASE</span>
                    </a>
                </div>

                <div class="flex items-center gap-3 text-xs font-bold">
                    <span class="text-zinc-300">Staff Rank: <strong class="text-amber-400">Rank {{ auth()->user()->rank }}</strong></span>
                    <a href="{{ url('/user/me') }}" class="px-3 py-1.5 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 transition">Exit ASE &rarr;</a>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 py-8 space-y-6">

            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 font-bold text-xs">
                    {{ session('success') }}
                </div>
            @endif

            <!-- DYNAMIC WELCOME MESSAGE BANNER -->
            <div class="bg-zinc-900/90 backdrop-blur-md border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 shadow-2xl space-y-4">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-extrabold text-amber-400">Housekeeping Administration Console</h1>
                        <p class="text-xs text-zinc-300 mt-1">{{ $welcomeMessage }}</p>
                    </div>

                    <div class="flex items-center gap-4 shrink-0">
                        <div class="px-4 py-2 rounded-2xl bg-black/60 border border-amber-500/30 text-center">
                            <span class="block text-[10px] font-bold text-zinc-400 uppercase">Total Accounts</span>
                            <span class="text-sm font-extrabold text-amber-400">{{ number_format(DB::table('users')->count()) }}</span>
                        </div>
                        <div class="px-4 py-2 rounded-2xl bg-black/60 border border-red-500/30 text-center">
                            <span class="block text-[10px] font-bold text-zinc-400 uppercase">Active Bans</span>
                            <span class="text-sm font-extrabold text-red-400">{{ number_format(DB::table('bans')->where('ban_expire', '>', time())->count()) }}</span>
                        </div>
                    </div>
                </div>

                @if(canAccessHkPermission('edit_welcome_message'))
                    <form method="POST" action="{{ route('housekeeping.dashboard') }}" class="pt-2 border-t border-amber-500/20 flex gap-2">
                        @csrf
                        <input type="text" name="welcome_message" value="{{ $welcomeMessage }}" class="w-full px-3 py-1.5 rounded-xl bg-black/60 border border-amber-500/30 text-xs font-bold text-white focus:outline-none">
                        <button type="submit" class="px-4 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-zinc-950 font-black text-xs uppercase tracking-wider shrink-0">Update Message</button>
                    </form>
                @endif
            </div>

            <!-- PINNED NOTICES WIDGET -->
            @if($pinnedNotices->count() > 0)
                <div class="bg-amber-950/30 border border-amber-500/50 rounded-3xl p-5 space-y-3">
                    <h2 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider flex items-center justify-between border-b border-amber-500/20 pb-2">
                        <span>Pinned Staff Announcements</span>
                        <a href="{{ url('/housekeeping/notices') }}" class="text-[10px] text-amber-300 underline">View Noticeboard &rarr;</a>
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @foreach($pinnedNotices as $notice)
                            <div class="p-3 rounded-2xl bg-black/50 border border-amber-500/30 space-y-1">
                                <h3 class="font-extrabold text-xs text-amber-300">{{ $notice->title }}</h3>
                                <p class="text-[10px] text-zinc-400 line-clamp-2">{{ $notice->content }}</p>
                                <p class="text-[9px] text-zinc-500 font-mono pt-1">By {{ $notice->username }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- CATEGORIZED MODULE GRID -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- USER & MODERATION SUITE -->
                <div class="bg-zinc-900/90 border border-amber-500/30 rounded-3xl p-6 space-y-4 shadow-xl flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between border-b border-amber-500/20 pb-3">
                            <h2 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider">User & Moderation</h2>
                            <img src="https://loungehotel.org/assets/images/icons/credits.png" class="h-4 w-4 object-contain" alt="">
                        </div>
                        <p class="text-[11px] text-zinc-400 leading-relaxed">Manage user credentials, balances, furniture, punishments, and audit game dialogue.</p>

                        <div class="space-y-2 pt-1">
                            @if(canAccessHkPermission('manage_users'))
                                <a href="{{ url('/housekeeping/users') }}" class="block p-3 rounded-2xl bg-black/50 border border-amber-500/20 hover:border-amber-400/50 text-xs font-bold text-zinc-200 hover:text-amber-300 transition flex items-center justify-between">
                                    <span>Users, Currencies & Furni</span>
                                    <span class="font-mono text-zinc-500">&rarr;</span>
                                </a>
                            @endif

                            @if(canAccessHkPermission('manage_tickets'))
                                <a href="{{ url('/housekeeping/tickets') }}" class="block p-3 rounded-2xl bg-black/50 border border-amber-500/20 hover:border-amber-400/50 text-xs font-bold text-zinc-200 hover:text-amber-300 transition flex items-center justify-between">
                                    <span>Help Center Tickets Manager</span>
                                    <span class="font-mono text-zinc-500">&rarr;</span>
                                </a>
                            @endif

                            @if(canAccessHkPermission('manage_bans'))
                                <a href="{{ url('/housekeeping/bans') }}" class="block p-3 rounded-2xl bg-black/50 border border-amber-500/20 hover:border-amber-400/50 text-xs font-bold text-zinc-200 hover:text-amber-300 transition flex items-center justify-between">
                                    <span>Ban Center (Account, IP & Machine)</span>
                                    <span class="font-mono text-zinc-500">&rarr;</span>
                                </a>
                            @endif

                            @if(canAccessHkPermission('manage_logs'))
                                <a href="{{ url('/housekeeping/chatlogs') }}" class="block p-3 rounded-2xl bg-black/50 border border-amber-500/20 hover:border-amber-400/50 text-xs font-bold text-zinc-200 hover:text-amber-300 transition flex items-center justify-between">
                                    <span>Chatlogs Auditor (Public & Private)</span>
                                    <span class="font-mono text-zinc-500">&rarr;</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- CONTENT & COMMERCE -->
                <div class="bg-zinc-900/90 border border-amber-500/30 rounded-3xl p-6 space-y-4 shadow-xl flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between border-b border-amber-500/20 pb-3">
                            <h2 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider">Content & Shop</h2>
                            <img src="https://loungehotel.org/assets/images/icons/diamonds.png" class="h-4 w-4 object-contain" alt="">
                        </div>
                        <p class="text-[11px] text-zinc-400 leading-relaxed">Publish news articles, update web catalog packages, badges, and pricing tiers.</p>

                        <div class="space-y-2 pt-1">
                            @if(canAccessHkPermission('create_article'))
                                <a href="{{ url('/housekeeping/articles/create') }}" class="block p-3 rounded-2xl bg-black/50 border border-amber-500/20 hover:border-amber-400/50 text-xs font-bold text-zinc-200 hover:text-amber-300 transition flex items-center justify-between">
                                    <span>+ Create News Article</span>
                                    <span class="font-mono text-zinc-500">&rarr;</span>
                                </a>
                            @endif

                            @if(canAccessHkPermission('edit_article'))
                                <a href="{{ url('/housekeeping/articles') }}" class="block p-3 rounded-2xl bg-black/50 border border-amber-500/20 hover:border-amber-400/50 text-xs font-bold text-zinc-200 hover:text-amber-300 transition flex items-center justify-between">
                                    <span>Manage Articles & Banners</span>
                                    <span class="font-mono text-zinc-500">&rarr;</span>
                                </a>
                            @endif

                            @if(canAccessHkPermission('manage_shop'))
                                <a href="{{ url('/housekeeping/shop') }}" class="block p-3 rounded-2xl bg-black/50 border border-amber-500/20 hover:border-amber-400/50 text-xs font-bold text-zinc-200 hover:text-amber-300 transition flex items-center justify-between">
                                    <span>Web Shop Packages & Badges</span>
                                    <span class="font-mono text-zinc-500">&rarr;</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- SYSTEM & SECURITY -->
                <div class="bg-zinc-900/90 border border-amber-500/30 rounded-3xl p-6 space-y-4 shadow-xl flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between border-b border-amber-500/20 pb-3">
                            <h2 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider">System Controls</h2>
                            <img src="https://loungehotel.org/assets/images/icons/duckets.png" class="h-4 w-4 object-contain" alt="">
                        </div>
                        <p class="text-[11px] text-zinc-400 leading-relaxed">Review staff notices, incoming applications, permissions, and hotel settings.</p>

                        <div class="space-y-2 pt-1">
                            @if(canAccessHkPermission('manage_noticeboard'))
                                <a href="{{ url('/housekeeping/notices') }}" class="block p-3 rounded-2xl bg-black/50 border border-amber-500/20 hover:border-amber-400/50 text-xs font-bold text-zinc-200 hover:text-amber-300 transition flex items-center justify-between">
                                    <span>Staff Noticeboard</span>
                                    <span class="font-mono text-zinc-500">&rarr;</span>
                                </a>
                            @endif

                            @if(canAccessHkPermission('manage_applications'))
                                <a href="{{ url('/housekeeping/applications') }}" class="block p-3 rounded-2xl bg-black/50 border border-amber-500/20 hover:border-amber-400/50 text-xs font-bold text-zinc-200 hover:text-amber-300 transition flex items-center justify-between">
                                    <span>Staff Applications</span>
                                    <span class="font-mono text-zinc-500">&rarr;</span>
                                </a>
                            @endif

                            @if(canAccessHkPermission('manage_permissions'))
                                <a href="{{ url('/housekeeping/permissions') }}" class="block p-3 rounded-2xl bg-black/50 border border-amber-500/20 hover:border-amber-400/50 text-xs font-bold text-zinc-200 hover:text-amber-300 transition flex items-center justify-between">
                                    <span>ASE Staff Permissions</span>
                                    <span class="font-mono text-zinc-500">&rarr;</span>
                                </a>
                            @endif

                            @if(canAccessHkPermission('manage_settings'))
                                <a href="{{ url('/housekeeping/settings') }}" class="block p-3 rounded-2xl bg-black/50 border border-amber-500/20 hover:border-amber-400/50 text-xs font-bold text-zinc-200 hover:text-amber-300 transition flex items-center justify-between">
                                    <span>Site & Hotel Settings</span>
                                    <span class="font-mono text-zinc-500">&rarr;</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

        </main>
    </div>
</body>
</html>