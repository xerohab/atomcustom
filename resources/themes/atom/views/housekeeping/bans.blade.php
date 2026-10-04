<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solace ASE - Moderation & Bans</title>

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

        <!-- ASE HEADER -->
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

        <!-- MAIN CONTAINER -->
        <main class="max-w-7xl mx-auto px-4 py-8 space-y-6">

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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- ISSUE BAN FORM -->
                <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 shadow-xl space-y-4 h-fit">
                    <h2 class="text-sm font-extrabold text-amber-400 uppercase tracking-wide border-b border-amber-500/20 pb-3">Issue Hotel Ban</h2>

                    <form action="{{ url('/housekeeping/bans') }}" method="POST" class="space-y-4 text-xs">
                        @csrf

                        <div>
                            <label class="block font-bold text-amber-300 mb-1">Target Username or IP</label>
                            <input type="text" name="value" required placeholder="e.g. Username or 127.0.0.1" class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                        </div>

                        <div>
                            <label class="block font-bold text-amber-300 mb-1">Ban Type</label>
                            <select name="type" class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                                <option value="account">Account Ban</option>
                                <option value="ip">IP Address Ban (Auto-pulls if username provided)</option>
                                <option value="machine">Machine/Hardware Ban (Auto-pulls if username provided)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-amber-300 mb-1">Duration</label>
                            <select name="duration" class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                                <option value="7200">2 Hours</option>
                                <option value="86400">24 Hours</option>
                                <option value="604800">7 Days</option>
                                <option value="2592000">30 Days</option>
                                <option value="315360000">10 Years (Permanent)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-amber-300 mb-1">Reason for Ban</label>
                            <textarea name="ban_reason" rows="3" required placeholder="Rule violation details..." class="w-full p-3 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500"></textarea>
                        </div>

                        <button type="submit" class="w-full py-2.5 rounded-xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 font-bold text-amber-100 uppercase tracking-wider transition shadow-lg">Apply Ban</button>
                    </form>
                </div>

                <!-- ACTIVE BANS TABLE -->
                <div class="lg:col-span-2 bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 lg:p-8 shadow-xl space-y-6">
                    <div class="border-b border-amber-500/20 pb-4">
                        <h1 class="text-xl font-extrabold text-amber-400 uppercase tracking-wide">Active Ban Records</h1>
                        <p class="text-xs text-zinc-400 mt-0.5">Manage active user, IP, and hardware exclusions. Extend or modify ban restrictions instantly.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-amber-500/20 text-amber-300 uppercase tracking-wider text-[10px]">
                                    <th class="pb-3 px-2">ID</th>
                                    <th class="pb-3 px-2">Type</th>
                                    <th class="pb-3 px-2">Target</th>
                                    <th class="pb-3 px-2">Reason</th>
                                    <th class="pb-3 px-2">Expires</th>
                                    <th class="pb-3 px-2 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-500/10 text-zinc-300">
                                @forelse($bans as $ban)
                                    <tr class="hover:bg-black/30 transition">
                                        <td class="py-3 px-2 font-mono text-zinc-500">#{{ $ban->id }}</td>
                                        <td class="py-3 px-2">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-red-950 border border-red-500/40 text-red-300">
                                                {{ $ban->type }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-2 font-bold text-white font-mono">
                                            {{ $ban->target_display }}
                                        </td>
                                        <td class="py-3 px-2 text-zinc-400 max-w-xs truncate">{{ $ban->ban_reason }}</td>
                                        <td class="py-3 px-2 text-zinc-400">{{ date('d M Y, H:i', $ban->ban_expire) }}</td>
                                        <td class="py-3 px-2 text-right flex justify-end gap-1.5">
                                            <button type="button" onclick='openEditBanModal(@json($ban))' class="px-2.5 py-1 rounded-lg bg-black/60 border border-amber-500/30 text-amber-300 font-bold hover:bg-amber-500/20 transition">Edit</button>

                                            <form action="{{ url('/housekeeping/bans/' . $ban->id) }}" method="POST" onsubmit="return confirm('Revoke this ban?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-950 border border-emerald-500/40 text-emerald-300 font-bold hover:bg-emerald-900 transition">Unban</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-zinc-500 italic">No active bans found in database.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-4 border-t border-amber-500/20">
                        {{ $bans->links() }}
                    </div>
                </div>

            </div>

        </main>
    </div>

    <!-- EDIT / EXTEND BAN MODAL -->
    <div id="editBanModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-zinc-900 border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 max-w-md w-full space-y-4 relative shadow-2xl">
            <div class="flex justify-between items-center border-b border-amber-500/20 pb-3">
                <h3 class="text-base font-extrabold text-amber-400 uppercase tracking-wide">Modify / Extend Ban</h3>
                <button type="button" onclick="closeEditBanModal()" class="text-zinc-400 hover:text-white font-bold">&times;</button>
            </div>

            <form action="{{ url('/housekeeping/bans/update') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" id="modal_ban_id" name="ban_id">

                <div>
                    <label class="block font-bold text-amber-300 mb-1">Change Ban Type</label>
                    <select id="modal_ban_type" name="type" class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                        <option value="account">Account Ban</option>
                        <option value="ip">IP Address Ban</option>
                        <option value="machine">Machine/Hardware Ban</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-amber-300 mb-1">Extend Duration</label>
                    <select name="extension" class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                        <option value="0">Keep Current Expiry</option>
                        <option value="86400">+ 24 Hours</option>
                        <option value="604800">+ 7 Days</option>
                        <option value="2592000">+ 30 Days</option>
                        <option value="315360000">+ 10 Years (Permanent)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-amber-300 mb-1">Update Reason</label>
                    <textarea id="modal_ban_reason" name="ban_reason" rows="3" required class="w-full p-3 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500"></textarea>
                </div>

                <div class="pt-3 border-t border-amber-500/20 flex justify-end gap-2">
                    <button type="button" onclick="closeEditBanModal()" class="px-4 py-2 rounded-xl bg-black/60 border border-zinc-700 text-zinc-300 font-bold">Cancel</button>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 font-bold text-amber-100 uppercase tracking-wider">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditBanModal(ban) {
            document.getElementById('modal_ban_id').value = ban.id;
            document.getElementById('modal_ban_type').value = ban.type;
            document.getElementById('modal_ban_reason').value = ban.ban_reason || '';
            document.getElementById('editBanModal').classList.remove('hidden');
        }

        function closeEditBanModal() {
            document.getElementById('editBanModal').classList.add('hidden');
        }
    </script>
</body>
</html>