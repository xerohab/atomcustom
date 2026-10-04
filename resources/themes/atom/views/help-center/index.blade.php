<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solace Hotel - Help & Support Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { gold: { 300: '#fde047', 400: '#facc15', 500: '#eab308', 600: '#ca8a04' } } } } }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0f0507 url('https://solacehotel.pw/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png') no-repeat center center fixed; background-size: cover; }
    </style>
</head>
<body class="min-h-screen text-white relative antialiased flex flex-col justify-between">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-[2px] z-0"></div>
    <div class="relative z-10">

        <!-- HEADER / NAVIGATION -->
        <header class="w-full bg-zinc-950/90 backdrop-blur-md border-b border-amber-500/30 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <a href="{{ url('/user/me') }}">
                        <img src="https://solacehotel.pw/assets/images/Solace.png" alt="Solace Hotel Logo" class="h-9 w-auto">
                    </a>
                    <nav class="hidden md:flex items-center gap-5 text-xs font-bold uppercase tracking-wider">
                        <a href="{{ url('/user/me') }}"
                           class="{{ Request::is('user/me') || Request::is('me') ? 'text-amber-400 border-b-2 border-amber-400 pb-1' : 'text-zinc-300 hover:text-amber-300 transition' }}">
                            Home
                        </a>

                        @auth
                            <a href="{{ url('/profile/' . auth()->user()->username) }}"
                               class="{{ Request::is('profile/' . auth()->user()->username) ? 'text-amber-400 border-b-2 border-amber-400 pb-1' : 'text-zinc-300 hover:text-amber-300 transition' }}">
                                My Profile
                            </a>
                        @endauth

                        <a href="{{ route('shop.index') }}"
                           class="{{ Request::is('shop*') ? 'text-amber-400 border-b-2 border-amber-400 pb-1' : 'text-zinc-300 hover:text-amber-300 transition' }}">
                            Shop
                        </a>

                        <div class="relative group py-1">
                            <button type="button" class="flex items-center gap-1 {{ Request::is('community*') ? 'text-amber-400 border-b-2 border-amber-400 pb-1' : 'text-zinc-300 hover:text-amber-300 transition' }}">
                                Community
                                <svg class="w-3 h-3 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div class="absolute left-0 mt-2 w-48 rounded-2xl bg-zinc-950/95 border border-amber-500/30 shadow-2xl py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <a href="{{ url('/community/photos') }}" class="block px-4 py-2 text-xs font-bold text-zinc-300 hover:bg-amber-500/10 hover:text-amber-400 transition">
                                    Camera Photos
                                </a>
                                <a href="{{ url('/community/staff') }}" class="block px-4 py-2 text-xs font-bold text-zinc-300 hover:bg-amber-500/10 hover:text-amber-400 transition">
                                    Hotel Staff
                                </a>
                                <a href="{{ url('/community/staff-applications') }}" class="block px-4 py-2 text-xs font-bold text-zinc-300 hover:bg-amber-500/10 hover:text-amber-400 transition">
                                    Staff Applications
                                </a>
                            </div>
                        </div>

                        <a href="{{ url('/community/articles') }}"
                           class="{{ Request::is('community/articles*') ? 'text-amber-400 border-b-2 border-amber-400 pb-1' : 'text-zinc-300 hover:text-amber-300 transition' }}">
                            News
                        </a>

                        <a href="{{ route('help-center.index') }}"
                           class="{{ Request::is('help-center*') ? 'text-amber-400 border-b-2 border-amber-400 pb-1' : 'text-zinc-300 hover:text-amber-300 transition' }}">
                            Help Center
                        </a>

                        @if(setting('discord_invitation_link'))
                            <a href="{{ setting('discord_invitation_link') }}" target="_blank" class="text-indigo-400 hover:text-indigo-300 transition">
                                Discord
                            </a>
                        @endif

                        @if(canAccessHkPermission('housekeeping_access'))
                            <a href="{{ url('/housekeeping') }}" class="text-red-400 hover:text-red-300 font-extrabold transition">
                                Housekeeping
                            </a>
                        @endif
                    </nav>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-black/60 border border-amber-500/30 text-xs font-bold text-amber-400">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        {{ $onlineUsersCount ?? 0 }} users online
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 font-bold text-xs uppercase tracking-wider transition">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 py-8 space-y-6">
            <div class="bg-zinc-900/90 backdrop-blur-md border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 shadow-2xl flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-amber-400">Help & Support Center</h1>
                    <p class="text-xs text-zinc-300 mt-1">Submit support tickets, ban appeals, or request room advertising directly from hotel staff.</p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('help-center.ticket.create') }}" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-zinc-950 font-black text-xs uppercase tracking-wider transition">+ Open Ticket</a>
                    <a href="{{ route('help-center.ticket.index') }}" class="px-4 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-amber-300 font-bold text-xs transition">My Tickets</a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 bg-zinc-900/90 border border-amber-500/30 rounded-3xl space-y-2 shadow-xl">
                    <h3 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider">Account & Appeals</h3>
                    <p class="text-[11px] text-zinc-400 leading-relaxed">Submit ban appeals or account whitelisting requests to senior administration.</p>
                </div>

                <div class="p-6 bg-zinc-900/90 border border-amber-500/30 rounded-3xl space-y-2 shadow-xl">
                    <h3 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider">General Assistance</h3>
                    <p class="text-[11px] text-zinc-400 leading-relaxed">Get help with game features, site issues, or general hotel inquiries.</p>
                </div>

                <div class="p-6 bg-zinc-900/90 border border-amber-500/30 rounded-3xl space-y-2 shadow-xl">
                    <h3 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider">Ads & Reports</h3>
                    <p class="text-[11px] text-zinc-400 leading-relaxed">Request official room ad promotion or submit evidence regarding scammers.</p>
                </div>
            </div>

            @if($myTickets->count() > 0)
                <div class="p-6 bg-zinc-900/90 border border-amber-500/30 rounded-3xl space-y-3 shadow-xl">
                    <h3 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider border-b border-amber-500/20 pb-2">Recent Support Requests</h3>

                    <div class="space-y-2">
                        @foreach($myTickets as $ticket)
                            <a href="{{ route('help-center.ticket.show', $ticket->id) }}" class="p-3 rounded-2xl bg-black/50 border border-amber-500/20 hover:border-amber-400/50 flex items-center justify-between text-xs transition block">
                                <div>
                                    <p class="font-extrabold text-zinc-200">#{{ $ticket->id }}: {{ $ticket->subject }}</p>
                                    <p class="text-[10px] text-zinc-500 uppercase font-bold">{{ str_replace('_', ' ', $ticket->department) }}</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-extrabold uppercase {{ $ticket->status === 'open' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' }}">
                                    {{ $ticket->status }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </main>
    </div>
</body>
</html>