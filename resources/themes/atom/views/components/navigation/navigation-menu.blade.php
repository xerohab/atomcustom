<header class="w-full bg-zinc-950/90 backdrop-blur-md border-b border-amber-500/30 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 py-3">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-6 min-w-0">
                <a href="{{ url('/user/me') }}" class="shrink-0">
                    <img src="https://solacehotel.pw/assets/images/Solace.png" alt="Solace Hotel Logo" class="h-8 sm:h-9 w-auto">
                </a>

                <nav class="hidden md:flex items-center gap-5 text-xs font-bold uppercase tracking-wider">
                    <a href="{{ url('/user/me') }}"
                       class="{{ Request::is('user/me') || Request::is('me') ? 'text-amber-400 border-b-2 border-amber-400 pb-1' : 'text-zinc-300 hover:text-amber-300 transition' }}">Home</a>
                    @auth
                        <a href="{{ url('/profile/' . auth()->user()->username) }}"
                           class="{{ Request::is('profile/' . auth()->user()->username) ? 'text-amber-400 border-b-2 border-amber-400 pb-1' : 'text-zinc-300 hover:text-amber-300 transition' }}">My Profile</a>
                    @endauth
                    <a href="{{ route('shop.index') }}"
                       class="{{ Request::is('shop*') ? 'text-amber-400 border-b-2 border-amber-400 pb-1' : 'text-zinc-300 hover:text-amber-300 transition' }}">Shop</a>

                    <div class="relative group py-1">
                        <button type="button" class="flex items-center gap-1 {{ Request::is('community*') ? 'text-amber-400 border-b-2 border-amber-400 pb-1' : 'text-zinc-300 hover:text-amber-300 transition' }}">
                            Community
                            <svg class="w-3 h-3 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div class="absolute left-0 mt-2 w-48 rounded-2xl bg-zinc-950/95 border border-amber-500/30 shadow-2xl py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <a href="{{ url('/community/photos') }}" class="block px-4 py-2 text-xs font-bold text-zinc-300 hover:bg-amber-500/10 hover:text-amber-400 transition">Camera Photos</a>
                            <a href="{{ url('/community/staff') }}" class="block px-4 py-2 text-xs font-bold text-zinc-300 hover:bg-amber-500/10 hover:text-amber-400 transition">Hotel Staff</a>
                            <a href="{{ url('/community/staff-applications') }}" class="block px-4 py-2 text-xs font-bold text-zinc-300 hover:bg-amber-500/10 hover:text-amber-400 transition">Staff Applications</a>
                        </div>
                    </div>

                    <a href="{{ url('/community/articles') }}" class="{{ Request::is('community/articles*') ? 'text-amber-400 border-b-2 border-amber-400 pb-1' : 'text-zinc-300 hover:text-amber-300 transition' }}">News</a>
                    <a href="{{ route('help-center.index') }}" class="{{ Request::is('help-center*') ? 'text-amber-400 border-b-2 border-amber-400 pb-1' : 'text-zinc-300 hover:text-amber-300 transition' }}">Help Center</a>
                    @if(setting('discord_invitation_link'))
                        <a href="{{ setting('discord_invitation_link') }}" target="_blank" class="text-indigo-400 hover:text-indigo-300 transition">Discord</a>
                    @endif
                    @if(canAccessHkPermission('housekeeping_access'))
                        <a href="{{ url('/housekeeping') }}" class="text-red-400 hover:text-red-300 font-extrabold transition">Housekeeping</a>
                    @endif
                </nav>
            </div>

            <div class="hidden md:flex items-center gap-3 shrink-0">
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-black/60 border border-amber-500/30 text-xs font-bold text-amber-400">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    {{ $onlineUsersCount ?? 0 }} users online
                </div>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 font-bold text-xs uppercase tracking-wider transition">Logout</button>
                </form>
            </div>

            <details class="md:hidden relative shrink-0 group z-[9998]">
                <summary class="list-none cursor-pointer h-10 w-11 flex items-center justify-center rounded-xl bg-amber-500/10 border border-amber-500/40 text-amber-400 [&::-webkit-details-marker]:hidden" aria-label="Open menu">
                    <svg class="w-6 h-6 group-open:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg class="w-6 h-6 hidden group-open:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </summary>
                <div class="solace-mobile-nav-panel fixed left-3 right-3 top-[68px] rounded-2xl border border-amber-500/40 shadow-2xl p-3 max-h-[calc(100vh-84px)] overflow-y-auto" style="background-color: rgb(9 9 11); z-index: 99999; pointer-events: auto; isolation: isolate;">
                    <div class="grid grid-cols-2 gap-2 text-xs font-extrabold uppercase tracking-wide">
                        <a href="{{ url('/user/me') }}" class="px-3 py-3 rounded-xl bg-black/50 border border-amber-500/20 text-amber-300">Home</a>
                        @auth<a href="{{ url('/profile/' . auth()->user()->username) }}" class="px-3 py-3 rounded-xl bg-black/50 border border-amber-500/20 text-zinc-200">My Profile</a>@endauth
                        <a href="{{ route('shop.index') }}" class="px-3 py-3 rounded-xl bg-black/50 border border-amber-500/20 text-zinc-200">Shop</a>
                        <a href="{{ url('/community/articles') }}" class="px-3 py-3 rounded-xl bg-black/50 border border-amber-500/20 text-zinc-200">News</a>
                        <a href="{{ url('/community/photos') }}" class="px-3 py-3 rounded-xl bg-black/50 border border-amber-500/20 text-zinc-200">Photos</a>
                        <a href="{{ url('/community/staff') }}" class="px-3 py-3 rounded-xl bg-black/50 border border-amber-500/20 text-zinc-200">Staff</a>
                        <a href="{{ url('/community/staff-applications') }}" class="px-3 py-3 rounded-xl bg-black/50 border border-amber-500/20 text-zinc-200">Applications</a>
                        <a href="{{ route('help-center.index') }}" class="px-3 py-3 rounded-xl bg-black/50 border border-amber-500/20 text-zinc-200">Help Center</a>
                        @if(setting('discord_invitation_link'))<a href="{{ setting('discord_invitation_link') }}" target="_blank" class="px-3 py-3 rounded-xl bg-indigo-950/50 border border-indigo-500/30 text-indigo-300">Discord</a>@endif
                        @if(canAccessHkPermission('housekeeping_access'))<a href="{{ url('/housekeeping') }}" class="px-3 py-3 rounded-xl bg-red-950/50 border border-red-500/30 text-red-300">Housekeeping</a>@endif
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-2 border-t border-amber-500/20 pt-3">
                        <div class="flex items-center gap-2 text-[11px] font-bold text-amber-300"><span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>{{ $onlineUsersCount ?? 0 }} online</div>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="px-4 py-2 rounded-xl bg-red-950 border border-red-500/40 text-amber-300 font-extrabold text-[11px] uppercase">Logout</button></form>
                    </div>
                </div>
            </details>
        </div>
    </div>
</header>
