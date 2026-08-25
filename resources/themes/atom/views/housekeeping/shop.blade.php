<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lounge ASE - Shop Articles</title>

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

                <!-- CREATE SHOP ITEM FORM -->
                <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 shadow-xl space-y-4 h-fit">
                    <h2 class="text-sm font-extrabold text-amber-400 uppercase tracking-wide border-b border-amber-500/20 pb-3">Add Shop Article</h2>

                    <form action="{{ url('/housekeeping/shop') }}" method="POST" class="space-y-3 text-xs">
                        @csrf

                        <div>
                            <label class="block font-bold text-amber-300 mb-1">Item Name</label>
                            <input type="text" name="name" required placeholder="e.g. VIP Subscription 30 Days" class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                        </div>

                        <div>
                            <label class="block font-bold text-amber-300 mb-1">Description / Info</label>
                            <input type="text" name="info" required placeholder="Brief perk details..." class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-amber-300 mb-1">Price (Costs)</label>
                                <input type="number" name="costs" value="0" required class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block font-bold text-amber-300 mb-1">Give Rank (Optional)</label>
                                <input type="number" name="give_rank" placeholder="e.g. 2" class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block font-bold text-amber-300 mb-1">Credits</label>
                                <input type="number" name="credits" value="0" class="w-full px-2 py-1.5 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block font-bold text-amber-300 mb-1">Duckets</label>
                                <input type="number" name="duckets" value="0" class="w-full px-2 py-1.5 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block font-bold text-amber-300 mb-1">Diamonds</label>
                                <input type="number" name="diamonds" value="0" class="w-full px-2 py-1.5 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-amber-300 mb-1">Badge Code(s)</label>
                            <input type="text" name="badges" placeholder="e.g. VIP,ADM" class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                        </div>

                        <div>
                            <label class="block font-bold text-amber-300 mb-1">Icon URL</label>
                            <input type="text" name="icon_url" required placeholder="/assets/images/shop/vip.png" class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                        </div>

                        <button type="submit" class="w-full py-2.5 rounded-xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 font-bold text-amber-100 uppercase tracking-wider transition shadow-lg">+ Create Shop Item</button>
                    </form>
                </div>

                <!-- ARTICLES TABLE -->
                <div class="lg:col-span-2 bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 lg:p-8 shadow-xl space-y-6">
                    <div class="border-b border-amber-500/20 pb-4">
                        <h1 class="text-xl font-extrabold text-amber-400 uppercase tracking-wide">Website Shop Articles</h1>
                        <p class="text-xs text-zinc-400 mt-0.5">Manage web shop packages, rank unlocks, and currency bundles.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-amber-500/20 text-amber-300 uppercase tracking-wider text-[10px]">
                                    <th class="pb-3 px-2">ID</th>
                                    <th class="pb-3 px-2">Item Name</th>
                                    <th class="pb-3 px-2">Cost</th>
                                    <th class="pb-3 px-2">Rewards</th>
                                    <th class="pb-3 px-2 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-500/10 text-zinc-300">
                                @forelse($shopArticles as $item)
                                    <tr class="hover:bg-black/30 transition">
                                        <td class="py-3 px-2 font-mono text-zinc-500">#{{ $item->id }}</td>
                                        <td class="py-3 px-2 font-bold text-white flex items-center gap-2">
                                            @if($item->icon_url) <img src="{{ $item->icon_url }}" class="h-6 w-6 object-contain"> @endif
                                            {{ $item->name }}
                                        </td>
                                        <td class="py-3 px-2 font-bold text-amber-300">{{ number_format($item->costs) }}</td>
                                        <td class="py-3 px-2 text-zinc-400">
                                            @if($item->give_rank) Rank {{ $item->give_rank }} | @endif
                                            @if($item->credits) C: {{ $item->credits }} | @endif
                                            @if($item->diamonds) D: {{ $item->diamonds }} @endif
                                        </td>
                                        <td class="py-3 px-2 text-right flex justify-end gap-1.5">
                                            <button type="button" onclick='openEditShopModal(@json($item))' class="px-2.5 py-1 rounded-lg bg-black/60 border border-amber-500/30 text-amber-300 font-bold hover:bg-amber-500/20 transition">Edit</button>

                                            <form action="{{ url('/housekeeping/shop/' . $item->id) }}" method="POST" onsubmit="return confirm('Delete shop article?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-red-950 border border-red-500/40 text-red-300 font-bold hover:bg-red-900 transition">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-zinc-500 italic">No shop articles configured.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-4 border-t border-amber-500/20">
                        {{ $shopArticles->links() }}
                    </div>
                </div>

            </div>

        </main>
    </div>

    <!-- EDIT SHOP ARTICLE MODAL -->
    <div id="editShopModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-zinc-900 border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 max-w-lg w-full space-y-4 relative shadow-2xl">
            <div class="flex justify-between items-center border-b border-amber-500/20 pb-3">
                <h3 class="text-base font-extrabold text-amber-400 uppercase tracking-wide">Edit Shop Package</h3>
                <button type="button" onclick="closeEditShopModal()" class="text-zinc-400 hover:text-white font-bold">&times;</button>
            </div>

            <form action="{{ url('/housekeeping/shop/update') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" id="modal_shop_id" name="shop_id">

                <div>
                    <label class="block font-bold text-amber-300 mb-1">Item Name</label>
                    <input type="text" id="modal_shop_name" name="name" required class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                </div>

                <div>
                    <label class="block font-bold text-amber-300 mb-1">Description / Info</label>
                    <input type="text" id="modal_shop_info" name="info" required class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-amber-300 mb-1">Price (Costs)</label>
                        <input type="number" id="modal_shop_costs" name="costs" required class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block font-bold text-amber-300 mb-1">Give Rank (Optional)</label>
                        <input type="number" id="modal_shop_give_rank" name="give_rank" class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block font-bold text-amber-300 mb-1">Credits</label>
                        <input type="number" id="modal_shop_credits" name="credits" class="w-full px-2 py-1.5 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block font-bold text-amber-300 mb-1">Duckets</label>
                        <input type="number" id="modal_shop_duckets" name="duckets" class="w-full px-2 py-1.5 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block font-bold text-amber-300 mb-1">Diamonds</label>
                        <input type="number" id="modal_shop_diamonds" name="diamonds" class="w-full px-2 py-1.5 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-amber-300 mb-1">Badge Code(s)</label>
                    <input type="text" id="modal_shop_badges" name="badges" class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                </div>

                <div>
                    <label class="block font-bold text-amber-300 mb-1">Icon URL</label>
                    <input type="text" id="modal_shop_icon_url" name="icon_url" required class="w-full px-3 py-2 rounded-xl bg-black/50 border border-amber-500/30 text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                </div>

                <div class="pt-3 border-t border-amber-500/20 flex justify-end gap-2">
                    <button type="button" onclick="closeEditShopModal()" class="px-4 py-2 rounded-xl bg-black/60 border border-zinc-700 text-zinc-300 font-bold">Cancel</button>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 font-bold text-amber-100 uppercase tracking-wider">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditShopModal(item) {
            document.getElementById('modal_shop_id').value = item.id;
            document.getElementById('modal_shop_name').value = item.name || '';
            document.getElementById('modal_shop_info').value = item.info || '';
            document.getElementById('modal_shop_costs').value = item.costs || 0;
            document.getElementById('modal_shop_give_rank').value = item.give_rank || '';
            document.getElementById('modal_shop_credits').value = item.credits || 0;
            document.getElementById('modal_shop_duckets').value = item.duckets || 0;
            document.getElementById('modal_shop_diamonds').value = item.diamonds || 0;
            document.getElementById('modal_shop_badges').value = item.badges || '';
            document.getElementById('modal_shop_icon_url').value = item.icon_url || '';
            document.getElementById('editShopModal').classList.remove('hidden');
        }

        function closeEditShopModal() {
            document.getElementById('editShopModal').classList.add('hidden');
        }
    </script>
</body>
</html>