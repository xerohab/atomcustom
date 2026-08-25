<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lounge ASE - User Management</title>
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
        <header class="w-full bg-zinc-950/90 backdrop-blur-md border-b border-amber-500/30 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ url('/housekeeping') }}" class="flex items-center gap-2">
                        <img src="https://loungehotel.org/assets/images/lounge.png" alt="Lounge Logo" class="h-8 w-auto">
                        <span class="text-xs font-black uppercase tracking-widest text-amber-400 bg-red-950 border border-amber-500/40 px-2.5 py-1 rounded-lg">ASE</span>
                    </a>
                </div>
                <a href="{{ url('/housekeeping') }}" class="px-3 py-1.5 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 text-xs font-bold transition">&larr; Back to Dashboard</a>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 py-8 space-y-6">
            <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 lg:p-8 shadow-xl space-y-6">
                <h1 class="text-xl font-extrabold text-amber-400 uppercase tracking-wide border-b border-amber-500/20 pb-3">User Search & Management</h1>

                <form action="{{ url('/housekeeping/users') }}" method="GET" class="flex gap-3 max-w-md">
                    <input type="text" name="search" placeholder="Search by username or email..." class="flex-grow px-4 py-2.5 rounded-xl bg-black/50 border border-amber-500/30 text-white placeholder-zinc-500 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-900 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 font-bold text-amber-100 text-xs uppercase tracking-wider transition">Search</button>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-amber-500/20 text-amber-300 uppercase tracking-wider text-[10px]">
                                <th class="pb-3 px-2">User</th>
                                <th class="pb-3 px-2">Rank</th>
                                <th class="pb-3 px-2">Credits</th>
                                <th class="pb-3 px-2">Status</th>
                                <th class="pb-3 px-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-500/10 text-zinc-300">
                            <tr>
                                <td class="py-3 px-2 font-bold text-white flex items-center gap-2">
                                    <span class="text-amber-400">{{ auth()->user()->username }}</span>
                                </td>
                                <td class="py-3 px-2 text-amber-300 font-bold">Rank {{ auth()->user()->rank }}</td>
                                <td class="py-3 px-2 font-mono text-amber-200">{{ number_format(auth()->user()->credits ?? 0) }}</td>
                                <td class="py-3 px-2"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-950 border border-emerald-500/40 text-emerald-400">Active</span></td>
                                <td class="py-3 px-2 text-right">
                                    <button class="px-3 py-1 rounded-lg bg-black/60 border border-amber-500/30 text-amber-300 font-bold hover:bg-amber-500/20 transition">Edit User</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>