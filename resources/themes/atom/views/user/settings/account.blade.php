<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solace - Account Settings</title>

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
        <main class="max-w-4xl mx-auto px-4 py-8 space-y-6">

            <!-- HEADER HERO -->
            <div class="bg-zinc-900/90 backdrop-blur-md border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 shadow-2xl flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold text-amber-400">Settings</h1>
                    <p class="text-xs text-zinc-300 mt-1">
                        Manage your account preferences, motto, personal quote, email address, custom profile banner, profile background, and security.
                    </p>
                </div>
            </div>

            <!-- SETTINGS SUB-NAVIGATION -->
            <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-2xl p-2 flex flex-wrap gap-2">
                <a href="{{ url('/user/settings/account') }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ Request::is('user/settings/account*') ? 'bg-amber-500 text-black shadow-md' : 'text-zinc-300 hover:text-amber-400 hover:bg-black/40' }}">
                    Account
                </a>
                <a href="{{ url('/user/settings/password') }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ Request::is('user/settings/password*') ? 'bg-amber-500 text-black shadow-md' : 'text-zinc-300 hover:text-amber-400 hover:bg-black/40' }}">
                    Password
                </a>
                <a href="{{ url('/user/settings/session-logs') }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ Request::is('user/settings/session-logs*') ? 'bg-amber-500 text-black shadow-md' : 'text-zinc-300 hover:text-amber-400 hover:bg-black/40' }}">
                    Session Logs
                </a>
                <a href="{{ url('/user/settings/two-factor') }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ Request::is('user/settings/two-factor*') ? 'bg-amber-500 text-black shadow-md' : 'text-zinc-300 hover:text-amber-400 hover:bg-black/40' }}">
                    Two-Factor Auth
                </a>
            </div>

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

            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-red-950 border border-red-500/40 text-red-300 font-bold text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>• {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- ACCOUNT FORM CARD -->
            <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 lg:p-8 shadow-xl space-y-6">
                <h2 class="text-sm font-extrabold text-amber-400 uppercase tracking-wide border-b border-amber-500/20 pb-3">
                    Account Details
                </h2>

                <form action="{{ url('/user/settings/account') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Motto Field -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-amber-300">Motto</label>
                            <input type="text" name="motto" value="{{ old('motto', auth()->user()->motto) }}" required
                                   class="w-full px-4 py-2.5 rounded-xl bg-black/50 border border-amber-500/30 text-white placeholder-zinc-500 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>

                        <!-- Email Address Field -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-amber-300">Email Address</label>
                            <input type="email" name="mail" value="{{ old('mail', auth()->user()->mail) }}" required
                                   class="w-full px-4 py-2.5 rounded-xl bg-black/50 border border-amber-500/30 text-white placeholder-zinc-500 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                    </div>

                    <!-- PERSONAL QUOTE / PROMPT SECTION -->
                    <div class="space-y-3 pt-3 border-t border-amber-500/10">
                        <label class="block text-xs font-bold text-amber-300">About Me / Unique Quote Prompt</label>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <select name="profile_quote_prefix" class="md:col-span-1 px-3 py-2.5 rounded-xl bg-black/50 border border-amber-500/30 text-amber-200 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 font-bold truncate">
                                <option value="">Select a sentence starter...</option>
                                <option value="Something you wouldn't know about me..." {{ old('profile_quote_prefix', auth()->user()->profile_quote_prefix) === "Something you wouldn't know about me..." ? 'selected' : '' }}>Something you wouldn't know about me...</option>
                                <option value="When I'm not on Solace, you can find me..." {{ old('profile_quote_prefix', auth()->user()->profile_quote_prefix) === "When I'm not on Solace, you can find me..." ? 'selected' : '' }}>When I'm not on Solace, you can find me...</option>
                                <option value="My unshakeable hot take..." {{ old('profile_quote_prefix', auth()->user()->profile_quote_prefix) === "My unshakeable hot take..." ? 'selected' : '' }}>My unshakeable hot take...</option>
                                <option value="My favorite core memory..." {{ old('profile_quote_prefix', auth()->user()->profile_quote_prefix) === "My favorite core memory..." ? 'selected' : '' }}>My favorite core memory...</option>
                                <option value="The best piece of advice I ever received..." {{ old('profile_quote_prefix', auth()->user()->profile_quote_prefix) === "The best piece of advice I ever received..." ? 'selected' : '' }}>The best piece of advice I ever received...</option>
                                <option value="If I had 1,000,000 Credits, I would..." {{ old('profile_quote_prefix', auth()->user()->profile_quote_prefix) === "If I had 1,000,000 Credits, I would..." ? 'selected' : '' }}>If I had 1,000,000 Credits, I would...</option>
                                <option value="My guilty pleasure..." {{ old('profile_quote_prefix', auth()->user()->profile_quote_prefix) === "My guilty pleasure..." ? 'selected' : '' }}>My guilty pleasure...</option>
                                <option value="A random fun fact about me..." {{ old('profile_quote_prefix', auth()->user()->profile_quote_prefix) === "A random fun fact about me..." ? 'selected' : '' }}>A random fun fact about me...</option>
                                <option value="My dream room build in Solace is..." {{ old('profile_quote_prefix', auth()->user()->profile_quote_prefix) === "My dream room build in Solace is..." ? 'selected' : '' }}>My dream room build in Solace is...</option>
                                <option value="You can always win me over with..." {{ old('profile_quote_prefix', auth()->user()->profile_quote_prefix) === "You can always win me over with..." ? 'selected' : '' }}>You can always win me over with...</option>
                                <option value="The worst habit I have is..." {{ old('profile_quote_prefix', auth()->user()->profile_quote_prefix) === "The worst habit I have is..." ? 'selected' : '' }}>The worst habit I have is...</option>
                                <option value="My favorite quote of all time is..." {{ old('profile_quote_prefix', auth()->user()->profile_quote_prefix) === "My favorite quote of all time is..." ? 'selected' : '' }}>My favorite quote of all time is...</option>
                                <option value="If I could trade places with any hotel user..." {{ old('profile_quote_prefix', auth()->user()->profile_quote_prefix) === "If I could trade places with any hotel user..." ? 'selected' : '' }}>If I could trade places with any hotel user...</option>
                                <option value="My current gaming obsession..." {{ old('profile_quote_prefix', auth()->user()->profile_quote_prefix) === "My current gaming obsession..." ? 'selected' : '' }}>My current gaming obsession...</option>
                                <option value="The official rule of my guest rooms..." {{ old('profile_quote_prefix', auth()->user()->profile_quote_prefix) === "The official rule of my guest rooms..." ? 'selected' : '' }}>The official rule of my guest rooms...</option>
                            </select>

                            <textarea name="profile_quote" rows="3" maxlength="250" placeholder="Finish off the sentence here (Up to 3 lines)..."
                                      class="md:col-span-2 p-3 rounded-xl bg-black/50 border border-amber-500/30 text-white placeholder-zinc-500 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 resize-none">{{ old('profile_quote', auth()->user()->profile_quote) }}</textarea>
                        </div>
                    </div>

                    <!-- Profile Banner Upload Field -->
                    <div class="space-y-2 pt-3 border-t border-amber-500/10">
                        <label class="block text-xs font-bold text-amber-300">Custom Profile Banner / GIF</label>

                        @if(auth()->user()->profile_banner)
                            <div class="h-20 w-full max-w-sm rounded-xl overflow-hidden border border-amber-500/30 bg-black/60 relative mb-3">
                                <img src="{{ asset('storage/' . auth()->user()->profile_banner) }}" alt="Current Profile Banner" class="w-full h-full object-cover">
                                <span class="absolute bottom-1 right-2 text-[9px] font-bold uppercase bg-black/80 px-2 py-0.5 rounded text-amber-300">Current Banner</span>
                            </div>
                        @endif

                        <input type="file" name="profile_banner" accept="image/*"
                               class="block w-full text-xs text-zinc-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-extrabold file:bg-gradient-to-r file:from-red-900 file:to-amber-700 file:text-amber-100 hover:file:opacity-90 cursor-pointer bg-black/50 border border-amber-500/30 rounded-xl p-1.5">
                        <p class="text-[10px] text-zinc-400">Supports PNG, JPG, WEBP, and animated GIFs (Max: 5MB).</p>
                    </div>

                    <!-- Custom Profile Background Upload Field -->
                    <div class="space-y-2 pt-3 border-t border-amber-500/10">
                        <label class="block text-xs font-bold text-amber-300">Custom Profile Background Image / GIF</label>

                        @if(auth()->user()->profile_background)
                            <div class="h-28 w-full max-w-sm rounded-xl overflow-hidden border border-amber-500/30 bg-black/60 relative mb-3">
                                <img src="{{ asset('storage/' . auth()->user()->profile_background) }}" alt="Current Profile Background" class="w-full h-full object-cover">
                                <span class="absolute bottom-1 right-2 text-[9px] font-bold uppercase bg-black/80 px-2 py-0.5 rounded text-amber-300">Current Background</span>
                            </div>
                        @endif

                        <input type="file" name="profile_background" accept="image/*"
                               class="block w-full text-xs text-zinc-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-extrabold file:bg-gradient-to-r file:from-red-900 file:to-amber-700 file:text-amber-100 hover:file:opacity-90 cursor-pointer bg-black/50 border border-amber-500/30 rounded-xl p-1.5">
                        <p class="text-[10px] text-zinc-400">Displays full-page behind your profile card. Supports PNG, JPG, WEBP, and animated GIFs (Max: 5MB).</p>
                    </div>

                    <!-- SUBMIT BUTTON -->
                    <div class="pt-4 border-t border-amber-500/20 flex justify-end">
                        <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 font-bold text-amber-100 text-xs uppercase tracking-wider shadow-lg transition">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- DISCORD CONNECTED ACCOUNTS WIDGET -->
            <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 lg:p-8 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-amber-500/20 pb-3">
                    <h2 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="currentColor" viewBox="0 0 24 24"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.061 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.028zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/></svg>
                        Connected Accounts
                    </h2>
                </div>

                @if(auth()->user()->discord_id)
                    <div class="p-4 rounded-2xl bg-black/50 border border-indigo-500/30 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            @if(auth()->user()->discord_avatar)
                                <img src="https://cdn.discordapp.com/avatars/{{ auth()->user()->discord_id }}/{{ auth()->user()->discord_avatar }}.png" class="h-10 w-10 rounded-full border border-indigo-500/40 object-cover">
                            @else
                                <div class="h-10 w-10 rounded-full bg-indigo-950 border border-indigo-500/40 flex items-center justify-center font-bold text-indigo-300">DC</div>
                            @endif
                            <div>
                                <p class="font-extrabold text-white">{{ auth()->user()->discord_username }}</p>
                                <p class="text-[10px] text-emerald-400 font-bold">? Connected to Discord</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('settings.discord.disconnect') }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 text-red-300 text-xs font-bold transition">Unlink</button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center justify-between text-xs p-2 gap-4">
                        <p class="text-zinc-400 leading-relaxed">Connect your Discord account to display your handle and avatar on your hotel profile.</p>
                        <a href="{{ route('settings.discord.connect') }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 font-extrabold text-white uppercase text-xs tracking-wider transition shrink-0 flex items-center gap-2">
                            Connect Discord
                        </a>
                    </div>
                @endif
            </div>

        </main>
    </div>

</body>
</html>