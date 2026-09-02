<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lounge ASE - Housekeeping Settings</title>

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

        <!-- ASE HEADER -->
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

        <!-- MAIN CONTAINER -->
        <main class="max-w-5xl mx-auto px-4 py-8 space-y-6">

            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-950 border border-emerald-500/40 text-emerald-300 font-bold text-xs">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 lg:p-8 shadow-xl space-y-6">
                <div class="border-b border-amber-500/20 pb-4 flex justify-between items-center">
                    <div>
                        <h1 class="text-xl font-extrabold text-amber-400 uppercase tracking-wide">Housekeeping Settings</h1>
                        <p class="text-xs text-zinc-400 mt-0.5">Control minimum required staff ranks (`min_rank`) for panel tools dynamically.</p>
                    </div>
                </div>

                <form action="{{ url('/housekeeping/permissions') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-amber-500/20 text-amber-300 uppercase tracking-wider text-[10px]">
                                    <th class="pb-3 px-2">Permission Key</th>
                                    <th class="pb-3 px-2">Description / Scope</th>
                                    <th class="pb-3 px-2 text-right">Minimum Rank Required</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-500/10 text-zinc-300">
                                @foreach($permissions as $perm)
                                    <tr class="hover:bg-black/30 transition">
                                        <td class="py-3 px-2 font-mono font-bold text-amber-400">{{ $perm->permission }}</td>
                                        <td class="py-3 px-2 text-zinc-400 text-[11px]">
                                            @switch($perm->permission)
                                                @case('housekeeping_access') Access Housekeeping Dashboard @break
                                                @case('manage_users') Search & edit user accounts / reset passwords @break
                                                @case('create_article') Publish new news articles @break
                                                @case('edit_article') Modify existing news articles @break
                                                @case('delete_article') Remove news articles @break
                                                @case('manage_bans') Issue & revoke user, IP, or machine bans @break
                                                @case('manage_settings') Edit website branding & client configuration @break
                                                @case('manage_permissions') Modify rank permission levels @break
                                                @default Custom Permission Key
                                            @endswitch
                                        </td>
                                        <td class="py-3 px-2 text-right">
                                            <input type="number" name="permissions[{{ $perm->permission }}]" value="{{ $perm->min_rank }}" min="1" max="10" required class="w-20 px-3 py-1.5 rounded-xl bg-black/60 border border-amber-500/30 text-white text-center font-bold focus:outline-none focus:ring-1 focus:ring-amber-500">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-4 border-t border-amber-500/20 flex justify-end">
                        <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 font-bold text-amber-100 text-xs uppercase tracking-wider transition shadow-lg">Save Housekeeping Settings</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>