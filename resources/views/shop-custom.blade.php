<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ setting('hotel_name', 'Lounge') }} - Shop</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        gold: { 300: '#fde047', 400: '#facc15', 500: '#eab308', 600: '#ca8a04' }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0b0f19; }
    </style>
</head>
<body class="min-h-screen text-zinc-200 antialiased flex flex-col justify-between">

    <!-- HEADER NAVIGATION -->
    <header class="w-full bg-zinc-950/90 backdrop-blur-md border-b border-amber-500/30 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="{{ url('/user/me') }}">
                    <img src="https://loungehotel.org/assets/images/lounge.png" alt="Lounge Logo" class="h-9 w-auto">
                </a>
                <nav class="hidden md:flex items-center gap-5 text-xs font-bold uppercase tracking-wider">
                    <a href="{{ url('/user/me') }}" class="{{ Request::is('user/me') || Request::is('me') ? 'text-amber-400 border-b-2 border-amber-400 pb-1' : 'text-zinc-300 hover:text-amber-300 transition' }}">Home</a>
                    @auth
                        <a href="{{ url('/profile/' . auth()->user()->username) }}" class="{{ Request::is('profile/' . auth()->user()->username) ? 'text-amber-400 border-b-2 border-amber-400 pb-1' : 'text-zinc-300 hover:text-amber-300 transition' }}">My Profile</a>
                    @endauth
                    <a href="{{ route('shop.index') }}" class="text-amber-400 border-b-2 border-amber-400 pb-1">Shop</a>
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
                    @if(setting('discord_invitation_link'))
                        <a href="{{ setting('discord_invitation_link') }}" target="_blank" class="text-indigo-400 hover:text-indigo-300 transition">Discord</a>
                    @endif
                    @if(function_exists('canAccessHkPermission') && canAccessHkPermission('housekeeping_access'))
                        <a href="{{ url('/housekeeping') }}" class="text-red-400 hover:text-red-300 font-extrabold transition">Housekeeping</a>
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
                    <button type="submit" class="px-4 py-2 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 font-bold text-xs uppercase tracking-wider transition">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto w-full px-4 py-8 space-y-6">

        <!-- TERMS & CONDITIONS BANNER -->
        <div class="w-full py-3 px-6 text-center bg-amber-500 text-zinc-950 font-extrabold rounded-2xl shadow-xl flex items-center justify-between text-xs">
            <span>{{ __('Please make sure to read our shop terms before making a purchase.') }}</span>
            <button type="button" class="bg-black/20 hover:bg-black/40 text-zinc-950 px-3 py-1 rounded-lg underline font-black transition" onclick="document.getElementById('termsModal').classList.remove('hidden')">
                {{ __('Terms & Conditions') }}
            </button>
        </div>

        <!-- 3-COLUMN GRID STRUCTURE (3 - 6 - 3) -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">

            <!-- COLUMN 1: CATEGORIES -->
            <div class="md:col-span-3 space-y-4">
                <div class="bg-zinc-900/90 border border-zinc-800 rounded-3xl p-5 shadow-xl space-y-3">
                    <h2 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider border-b border-zinc-800 pb-2 flex items-center gap-2">
                        <img src="{{ asset('/assets/images/icons/navigation/shop.png') }}" class="h-5 w-5 object-contain">
                        {{ __('Categories') }}
                    </h2>

                    <div class="space-y-2 text-xs font-bold">
                        <a href="{{ route('shop.index') }}"
                           class="flex items-center gap-3 py-2.5 px-4 rounded-2xl border transition {{ !request()->route('category') ? 'bg-amber-500/20 border-amber-500/50 text-amber-300' : 'bg-zinc-950/60 border-zinc-800 text-zinc-300 hover:border-zinc-700' }}">
                            <img class="h-5 w-5 object-contain" src="{{ asset('/assets/images/icons/navigation/shop.png') }}" alt="All">
                            <span>{{ __('All Packages') }}</span>
                        </a>

                        @foreach($categories as $category)
                            <a href="{{ route('shop.index', $category->slug) }}"
                               class="flex items-center gap-3 py-2.5 px-4 rounded-2xl border transition {{ request()->route('category') === $category->slug ? 'bg-amber-500/20 border-amber-500/50 text-amber-300' : 'bg-zinc-950/60 border-zinc-800 text-zinc-300 hover:border-zinc-700' }}">
                                @if($category->icon)
                                    <img class="h-5 w-5 object-contain" src="{{ $category->icon }}" alt="{{ $category->name }}">
                                @endif
                                <span>{{ $category->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- COLUMN 2: SHOP PACKAGES (SORTED ASC BY PRICE) -->
            <div class="md:col-span-6 space-y-4">

                @if(session('success'))
                    <div class="p-4 rounded-2xl bg-emerald-950 border border-emerald-500/40 text-emerald-300 font-bold text-xs">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-4 rounded-2xl bg-red-950 border border-red-500/40 text-red-300 font-bold text-xs">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="space-y-4">
                    @forelse ($articles as $article)
                        <div class="bg-zinc-900/90 border border-amber-500/30 rounded-3xl p-5 shadow-xl space-y-4 flex flex-col justify-between hover:border-amber-400/50 transition">
                            <div class="space-y-3">
                                <div class="flex items-start justify-between border-b border-amber-500/20 pb-3 gap-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $article->icon_url ?: asset('/assets/images/icons/navigation/shop.png') }}"
                                             class="h-10 w-10 object-contain p-1 rounded-2xl bg-black/40 border border-amber-500/20"
                                             onerror="this.src='/assets/images/icons/navigation/shop.png'">
                                        <div>
                                            <h3 class="font-extrabold text-amber-400 text-sm">{{ $article->name }}</h3>
                                            <p class="text-[11px] text-zinc-400 leading-snug">{{ $article->info }}</p>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 rounded-xl bg-amber-950 border border-amber-500/40 text-amber-300 font-black text-xs shrink-0">
                                        ${{ number_format($article->costs, 2) }}
                                    </span>
                                </div>

                                <!-- CURRENCY & REWARD READOUTS WITH ICONS -->
                                <div class="grid grid-cols-2 gap-2 text-[11px]">
                                    @if($article->credits > 0)
                                        <div class="p-2.5 rounded-xl bg-black/50 border border-amber-500/10 text-zinc-300 flex items-center gap-2">
                                            <img src="https://loungehotel.org/assets/images/icons/credits.png" class="h-4 w-4 object-contain" alt="Credits" onerror="this.src='/assets/images/icons/navigation/shop.png'">
                                            <span>Credits:</span>
                                            <span class="text-amber-400 font-extrabold ml-auto">+{{ number_format($article->credits) }}</span>
                                        </div>
                                    @endif

                                    @if($article->duckets > 0)
                                        <div class="p-2.5 rounded-xl bg-black/50 border border-amber-500/10 text-zinc-300 flex items-center gap-2">
                                            <img src="https://loungehotel.org/assets/images/icons/duckets.png" class="h-4 w-4 object-contain" alt="Duckets" onerror="this.src='/assets/images/icons/navigation/shop.png'">
                                            <span>Duckets:</span>
                                            <span class="text-amber-400 font-extrabold ml-auto">+{{ number_format($article->duckets) }}</span>
                                        </div>
                                    @endif

                                    @if($article->diamonds > 0)
                                        <div class="p-2.5 rounded-xl bg-black/50 border border-amber-500/10 text-zinc-300 flex items-center gap-2">
                                            <img src="https://loungehotel.org/assets/images/icons/diamonds.png" class="h-4 w-4 object-contain" alt="Diamonds" onerror="this.src='/assets/images/icons/navigation/shop.png'">
                                            <span>Diamonds:</span>
                                            <span class="text-amber-400 font-extrabold ml-auto">+{{ number_format($article->diamonds) }}</span>
                                        </div>
                                    @endif

                                    @if($article->give_rank)
                                        <div class="p-2.5 rounded-xl bg-black/50 border border-amber-500/10 text-zinc-300 flex items-center gap-2">
                                            <img src="/assets/images/icons/navigation/staff.png" class="h-4 w-4 object-contain" alt="Rank">
                                            <span>Rank Reward:</span>
                                            <span class="text-amber-400 font-extrabold ml-auto">Rank #{{ $article->give_rank }}</span>
                                        </div>
                                    @endif
                                </div>

                                @if(!empty($article->badges))
                                    <div class="pt-2 border-t border-amber-500/10 flex items-center gap-2">
                                        <span class="text-[10px] text-zinc-500 font-bold uppercase">Badges:</span>
                                        @foreach(explode(',', $article->badges) as $badge)
                                            <span class="px-2 py-0.5 rounded-lg bg-black/60 border border-amber-500/20 text-amber-300 text-[10px] font-mono font-bold">{{ trim($badge) }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <form action="{{ route('shop.buy', $article->id) }}" method="POST">
                                @csrf
                                <button type="submit" onclick="return confirm('Purchase {{ $article->name }} for ${{ $article->costs }}?');" class="w-full py-2.5 rounded-xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 font-extrabold text-amber-100 uppercase tracking-wider text-xs transition shadow-lg">
                                    {{ __('Purchase Package') }}
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="p-12 text-center bg-zinc-900/90 border border-zinc-800 rounded-3xl text-zinc-500 italic text-xs">
                            {{ __('No shop packages available in this category.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- COLUMN 3: PAYPAL & VOUCHER -->
            <div class="md:col-span-3 space-y-4">
                <div class="bg-zinc-900/90 border border-zinc-800 rounded-3xl p-5 shadow-xl space-y-3">
                    <h2 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider border-b border-zinc-800 pb-2">
                        {{ __('Top Up Account') }}
                    </h2>

                    <div class="text-xs text-center py-2.5 px-4 rounded-2xl bg-black/60 border border-amber-500/20 text-white my-2">
                        <span class="block text-[10px] uppercase text-zinc-400 font-bold">{{ __('Current Balance') }}</span>
                        <span class="text-base font-black text-amber-400">${{ number_format(auth()->user()->website_balance ?? 0, 2) }}</span>
                    </div>

                    @if(config('paypal.live.client_id') || config('paypal.sandbox.client_id'))
                        <form action="{{ route('paypal.process-transaction') }}" method="GET" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block font-bold text-xs text-amber-300 mb-1">{{ __('Amount ($ USD)') }}</label>
                                <input type="number" name="amount" min="1" step="1" value="5" required class="w-full px-3 py-2 rounded-xl bg-black/50 border border-zinc-700/60 text-white focus:outline-none focus:ring-1 focus:ring-amber-500 text-xs font-bold">
                            </div>

                            <button type="submit" class="w-full py-2.5 rounded-xl bg-gradient-to-r from-blue-700 to-blue-600 hover:from-blue-600 hover:to-blue-500 border border-blue-400/40 font-bold text-white text-xs uppercase tracking-wider transition shadow-lg flex items-center justify-center gap-2">
                                <span>{{ __('Pay with PayPal') }}</span>
                            </button>
                        </form>
                    @else
                        <p class="text-zinc-500 text-xs text-center italic mt-2">
                            {{ __('PayPal credentials offline.') }}
                        </p>
                    @endif
                </div>

                <div class="bg-zinc-900/90 border border-zinc-800 rounded-3xl p-5 shadow-xl space-y-3">
                    <h2 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider border-b border-zinc-800 pb-2">
                        {{ __('Voucher') }}
                    </h2>

                    <form action="{{ route('shop.use-voucher') }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="text" name="code" placeholder="Enter voucher code..." required class="w-full px-3 py-2 rounded-xl bg-black/50 border border-zinc-700/60 text-white focus:outline-none focus:ring-1 focus:ring-amber-500 text-xs font-mono">

                        <button type="submit" class="w-full py-2.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 border border-amber-500/30 font-bold text-amber-300 text-xs transition">
                            {{ __('Redeem Voucher') }}
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </main>

    <!-- TERMS MODAL (CLEANED TEXT) -->
    <div id="termsModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-zinc-900 border-2 border-amber-500/40 rounded-3xl p-6 max-w-lg w-full space-y-4 relative shadow-2xl text-xs text-zinc-300">
            <div class="flex justify-between items-center border-b border-amber-500/20 pb-3">
                <h3 class="text-base font-extrabold text-amber-400 uppercase tracking-wide">Shop Terms & Conditions</h3>
                <button type="button" onclick="document.getElementById('termsModal').classList.add('hidden')" class="text-zinc-400 hover:text-white font-bold">&times;</button>
            </div>
            <p>Here at {{ setting('hotel_name', 'Lounge') }} we accept donations to pay our server hosting costs. In return, digital items are credited instantly to your account.</p>
            <p class="font-bold text-amber-300">Non-Refundable Policy</p>
            <p>All donations are non-refundable. Balance cannot be converted back into money. By purchasing, you agree not to initiate bank chargebacks.</p>
            <div class="pt-3 border-t border-amber-500/20 flex justify-end">
                <button type="button" onclick="document.getElementById('termsModal').classList.add('hidden')" class="px-5 py-2 rounded-xl bg-black/60 border border-zinc-700 text-zinc-300 font-bold">Close</button>
            </div>
        </div>
    </div>

</body>
</html>