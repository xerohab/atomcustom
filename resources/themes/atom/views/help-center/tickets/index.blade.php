<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lounge Hotel - My Support Tickets</title>
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

        <!-- HEADER / NAVIGATION -->
        <header class="w-full bg-zinc-950/90 backdrop-blur-md border-b border-amber-500/30 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <a href="{{ url('/user/me') }}">
                        <img src="https://loungehotel.org/assets/images/lounge.png" alt="Lounge Hotel Logo" class="h-9 w-auto">
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
            <div class="bg-zinc-900/90 backdrop-blur-md border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 shadow-2xl flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-extrabold text-amber-400">My Support Tickets</h1>
                    <p class="text-xs text-zinc-300 mt-1">Track responses and communicate with support staff.</p>
                </div>

                <a href="{{ route('help-center.ticket.create') }}" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-zinc-950 font-black text-xs uppercase tracking-wider transition">+ Open Ticket</a>
            </div>

            <div class="bg-zinc-900/90 border border-amber-500/30 rounded-3xl overflow-hidden shadow-xl">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-black/60 border-b border-amber-500/20 text-amber-400 font-extrabold uppercase">
                            <th class="p-4">Ticket ID</th>
                            <th class="p-4">Department</th>
                            <th class="p-4">Subject</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/80 text-zinc-300">
                        @forelse($tickets as $ticket)
                            <tr class="hover:bg-black/30 transition">
                                <td class="p-4 font-mono font-bold text-amber-400">#{{ $ticket->id }}</td>
                                <td class="p-4 font-bold uppercase text-[10px] text-amber-300">{{ str_replace('_', ' ', $ticket->department) }}</td>
                                <td class="p-4 font-semibold text-zinc-200">{{ $ticket->subject }}</td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 rounded-xl text-[10px] font-extrabold uppercase {{ $ticket->status === 'open' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' }}">
                                        {{ $ticket->status }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('help-center.ticket.show', $ticket->id) }}" class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-zinc-950 font-black text-xs uppercase tracking-wider transition">View Ticket</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-zinc-500 italic">No tickets found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pt-2">
                {{ $tickets->links() }}
            </div>
        </main>
    </div>
</body>
</html>