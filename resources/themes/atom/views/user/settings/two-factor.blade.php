<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solace - Two-Factor Authentication</title>

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
                        Manage your account preferences, motto, email address, and security.
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

            <!-- TWO-FACTOR FORM CARD -->
            <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 lg:p-8 shadow-xl space-y-6">
                <div class="flex justify-between items-center border-b border-amber-500/20 pb-3">
                    <div>
                        <h2 class="text-sm font-extrabold text-amber-400 uppercase tracking-wide">
                            Two-Factor Authentication (2FA)
                        </h2>
                        <p class="text-xs text-zinc-400 mt-0.5">
                            Add an extra layer of security to your Solace Hotel account using an authenticator app.
                        </p>
                    </div>

                    <!-- Status Badge -->
                    <span class="text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full border {{ auth()->user()->two_factor_secret ? 'bg-emerald-950/80 border-emerald-500/40 text-emerald-400' : 'bg-red-950/80 border-red-500/40 text-red-400' }}">
                        {{ auth()->user()->two_factor_secret ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>

                @if(!auth()->user()->two_factor_secret)
                    <!-- SETUP 2FA FORM -->
                    <form action="{{ url('/user/settings/two-factor') }}" method="POST" class="space-y-6">
                        @csrf

                        @if(isset($image))
                            <div class="flex flex-col sm:flex-row items-center gap-6 p-4 rounded-2xl bg-black/50 border border-amber-500/20">
                                <div class="bg-white p-2 rounded-xl flex-shrink-0">
                                    <img src="{{ $image }}" alt="QR Code" class="h-32 w-32">
                                </div>
                                <div class="space-y-2 text-xs text-zinc-300">
                                    <h3 class="font-bold text-amber-300 uppercase tracking-wider">Setup Instructions</h3>
                                    <p>1. Scan the QR code with Google Authenticator or Authy.</p>
                                    <p>2. Enter the 6-digit code generated by your app below to confirm activation.</p>
                                    @if(isset($secret))
                                        <div class="pt-1">
                                            <span class="text-[10px] font-bold text-zinc-400 block uppercase">Manual Secret Key:</span>
                                            <code class="text-amber-400 font-mono text-xs">{{ $secret }}</code>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="space-y-1.5 max-w-sm">
                            <label class="block text-xs font-bold text-amber-300">Authentication Code</label>
                            <input type="text" name="code" required placeholder="123456" maxlength="6"
                                   class="w-full px-4 py-2.5 rounded-xl bg-black/50 border border-amber-500/30 text-white placeholder-zinc-500 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono tracking-widest text-center">
                        </div>

                        <div class="pt-4 border-t border-amber-500/20 flex justify-end">
                            <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 font-bold text-amber-100 text-xs uppercase tracking-wider shadow-lg transition">
                                Enable Two-Factor
                            </button>
                        </div>
                    </form>
                @else
                    <!-- DISABLE 2FA FORM -->
                    <form action="{{ url('/user/settings/two-factor') }}" method="POST" class="space-y-5">
                        @csrf
                        @method('DELETE')

                        <p class="text-xs text-zinc-300 leading-relaxed">
                            Two-factor authentication is active on your account. To disable it, confirm your current account password below.
                        </p>

                        <div class="space-y-1.5 max-w-md">
                            <label class="block text-xs font-bold text-amber-300">Current Password</label>
                            <input type="password" name="password" required placeholder="••••••••"
                                   class="w-full px-4 py-2.5 rounded-xl bg-black/50 border border-amber-500/30 text-white placeholder-zinc-500 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>

                        <div class="pt-4 border-t border-amber-500/20 flex justify-end">
                            <button type="submit" class="px-8 py-3 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 font-bold text-red-200 text-xs uppercase tracking-wider shadow-lg transition">
                                Disable Two-Factor
                            </button>
                        </div>
                    </form>
                @endif

            </div>

        </main>
    </div>

</body>
</html>