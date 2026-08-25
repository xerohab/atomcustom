<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lounge - Welcome to LoungeHotel</title>

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
            background: #0f0507 url('https://loungehotel.org/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png') no-repeat center center fixed;
            background-size: cover;
        }

        .lounge-hero-bg {
            background-image: linear-gradient(to bottom, rgba(15, 5, 7, 0.75), rgba(15, 5, 7, 0.85)),
                              url('https://loungehotel.org/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 lg:p-12 relative overflow-x-hidden antialiased">

    <!-- Dark Page Overlay -->
    <div class="fixed inset-0 bg-black/60 backdrop-blur-[2px] z-0"></div>

    <!-- Main Container -->
    <main class="relative z-10 w-full max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">

            <!-- Left Hero Box -->
            <div class="lg:col-span-7 lounge-hero-bg border-2 border-amber-500/30 rounded-3xl p-8 lg:p-10 text-white shadow-2xl flex flex-col justify-between min-h-[500px] relative overflow-hidden">
                <div class="space-y-6 relative z-10">
                    <div class="flex items-center gap-3">
                        <img src="https://loungehotel.org/assets/images/lounge.png" alt="Lounge Hotel Logo" class="h-16 w-auto drop-shadow-lg">
                    </div>

                    <div class="space-y-2 pt-4">
                        <h1 class="text-4xl lg:text-5xl font-extrabold tracking-tight text-white">
                            Welcome to <span class="text-amber-400">LoungeHotel</span>
                        </h1>
                        <p class="text-amber-100/90 text-lg leading-relaxed max-w-lg font-medium drop-shadow-md">
                            Relax in comfort, experience luxury, and find your people at the Lounge.
                        </p>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="grid grid-cols-3 gap-3 pt-8 relative z-10">
                    <div class="bg-black/80 backdrop-blur-md border border-amber-500/40 rounded-2xl p-4 flex flex-col justify-center shadow-inner">
                        <!-- REAL-TIME ONLINE COUNTER -->
                        <div id="onlineCountDisplay" class="text-2xl font-black text-amber-400">
                            {{ $onlineCount ?? 0 }}
                        </div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-amber-200/70 mt-1">Users online</div>
                    </div>

                    <div class="bg-black/80 backdrop-blur-md border border-amber-500/40 rounded-2xl p-4 flex flex-col justify-center shadow-inner">
                        <div class="text-sm font-black text-red-400 uppercase">Friendly</div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-amber-200/70 mt-1">Community</div>
                    </div>

                    <div class="bg-black/80 backdrop-blur-md border border-amber-500/40 rounded-2xl p-4 flex flex-col justify-center shadow-inner">
                        <div class="text-sm font-black text-amber-400 uppercase">Newest</div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-amber-200/70 mt-1">Furniture</div>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Login Form -->
            <div class="lg:col-span-5 bg-zinc-900/95 backdrop-blur-md border-2 border-amber-500/40 rounded-3xl p-8 lg:p-10 shadow-2xl relative">

                <div class="flex justify-end mb-4">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-black/80 text-amber-400 border border-amber-500/40">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Hotel Status: <span class="text-emerald-400">Online</span>
                    </span>
                </div>

                <div class="text-center mb-6">
                    <h2 class="text-3xl font-extrabold text-amber-400">Welcome back</h2>
                    <p class="text-sm text-zinc-400 mt-1">We missed you! Sign in to continue your adventure.</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="username" class="block text-xs font-bold uppercase text-amber-200/80 mb-1">Username</label>
                        <input id="username" type="text" name="username" required autofocus
                               class="w-full px-4 py-3 rounded-xl bg-black/50 border border-amber-500/30 text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold uppercase text-amber-200/80 mb-1">Password</label>
                        <input id="password" type="password" name="password" required
                               class="w-full px-4 py-3 rounded-xl bg-black/50 border border-amber-500/30 text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div class="flex items-center justify-between text-xs py-1">
                        <label class="flex items-center text-zinc-400 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded border-amber-500 text-red-700 focus:ring-amber-500">
                            <span class="ml-2">Remember me</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full py-4 px-6 rounded-xl font-bold text-amber-100 bg-gradient-to-r from-red-900 via-red-800 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 shadow-xl transform active:scale-95 transition-all text-sm uppercase tracking-wide">
                        Enter Lounge &rarr;
                    </button>
                </form>

                <div class="mt-8 text-center text-xs text-zinc-400 border-t border-amber-500/20 pt-4">
                    New here?
                    <a href="{{ route('register') }}" class="font-bold text-amber-400 hover:underline">
                        Create your character &rarr;
                    </a>
                </div>
            </div>

        </div>
    </main>

    <!-- REAL-TIME ONLINE USERS UPDATER SCRIPT -->
    <script>
        function updateOnlineUserCount() {
            fetch('/api/online-count')
                .then(response => response.json())
                .then(data => {
                    if (data && typeof data.online_count !== 'undefined') {
                        document.getElementById('onlineCountDisplay').innerText = data.online_count;
                    }
                })
                .catch(error => console.error('Error updating online count:', error));
        }

        // Fetch user count immediately on page load
        updateOnlineUserCount();

        // Refresh every 10 seconds
        setInterval(updateOnlineUserCount, 10000);
    </script>

</body>
</html>