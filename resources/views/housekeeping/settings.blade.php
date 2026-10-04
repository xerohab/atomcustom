<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solace ASE - Site Settings</title>
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
        <header class="w-full bg-zinc-950/90 backdrop-blur-md border-b border-amber-500/30 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ url('/housekeeping') }}" class="flex items-center gap-2">
                        <img src="https://solacehotel.pw/assets/images/Solace.png" alt="Solace Logo" class="h-8 w-auto">
                        <span class="text-xs font-black uppercase tracking-widest text-amber-400 bg-red-950 border border-amber-500/40 px-2.5 py-1 rounded-lg">ASE</span>
                    </a>
                </div>
                <a href="{{ url('/housekeeping') }}" class="px-3 py-1.5 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 text-xs font-bold transition">&larr; Back to Dashboard</a>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 py-8 space-y-6">
            <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 lg:p-8 shadow-xl space-y-6">
                <h1 class="text-xl font-extrabold text-amber-400 uppercase tracking-wide border-b border-amber-500/20 pb-3">Hotel & Website Configuration</h1>

                <form action="#" method="POST" class="space-y-4 max-w-2xl">
                    @csrf
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-amber-300">Hotel Name</label>
                        <input type="text" name="hotel_name" value="{{ setting('hotel_name') ?? 'Solace Hotel' }}" class="w-full px-4 py-2.5 rounded-xl bg-black/50 border border-amber-500/30 text-white text-xs focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-amber-300">Avatar Imager Endpoint</label>
                        <input type="text" name="avatar_imager" value="{{ setting('avatar_imager') }}" class="w-full px-4 py-2.5 rounded-xl bg-black/50 border border-amber-500/30 text-white text-xs focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-amber-300">Discord Invite Link</label>
                        <input type="text" name="discord_invitation_link" value="{{ setting('discord_invitation_link') }}" class="w-full px-4 py-2.5 rounded-xl bg-black/50 border border-amber-500/30 text-white text-xs focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 font-bold text-amber-100 text-xs uppercase tracking-wider transition">Save Configuration</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>