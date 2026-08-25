<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lounge ASE - User Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { gold: { 300: '#fde047', 400: '#facc15', 500: '#eab308', 600: '#ca8a04' } } } } }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0f0507 url('https://loungehotel.org/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png') no-repeat center center fixed; background-size: cover; }
    </style>
</head>
<body class="min-h-screen text-white relative antialiased flex flex-col justify-between">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-[2px] z-0"></div>
    <div class="relative z-10">

        <!-- HEADER -->
        <header class="w-full bg-zinc-950/90 backdrop-blur-md border-b border-amber-500/30 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ url('/housekeeping') }}" class="flex items-center gap-2">
                        <img src="https://loungehotel.org/assets/images/lounge.png" alt="Lounge Logo" class="h-8 w-auto">
                        <span class="text-xs font-black uppercase tracking-widest text-amber-400 bg-red-950 border border-amber-500/40 px-2.5 py-1 rounded-lg">ASE</span>
                    </a>
                </div>

                <div class="flex items-center gap-3 text-xs font-bold">
                    <a href="{{ url('/housekeeping') }}" class="text-zinc-300 hover:text-amber-400 transition">&larr; Dashboard</a>
                    <a href="{{ url('/user/me') }}" class="px-3 py-1.5 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 transition">Exit ASE &rarr;</a>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 py-8 space-y-6">

            <!-- HEADER & SEARCH -->
            <div class="bg-zinc-900/90 backdrop-blur-md border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 shadow-2xl flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-amber-400">Manage Hotel Users</h1>
                    <p class="text-xs text-zinc-300 mt-1">Edit mottos, staff ranks, balances (Credits, Duckets, Diamonds), reset passwords, and inspect/delete user furniture.</p>
                </div>

                <form method="GET" action="{{ route('housekeeping.users') }}" class="flex gap-2 w-full md:w-auto">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search username or email..." class="px-3 py-2 rounded-xl bg-black/60 border border-amber-500/30 text-xs font-bold text-white focus:outline-none focus:ring-1 focus:ring-amber-500 w-full md:w-64">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-zinc-950 font-black text-xs uppercase tracking-wider transition">Search</button>
                </form>
            </div>

            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 font-bold text-xs">
                    {{ session('success') }}
                </div>
            @endif

            <!-- FURNITURE INSPECTOR DRAWER -->
            @if($inspectUser)
                <div class="bg-zinc-900/90 backdrop-blur-md border-2 border-amber-500/50 rounded-3xl p-6 space-y-6 shadow-2xl" x-data="{ selectedRoom: 'all' }">
                    <div class="flex items-center justify-between border-b border-amber-500/20 pb-4">
                        <div class="flex items-center gap-3">
                            <img src="https://www.habbo.com/habbo-imaging/avatarimage?figure={{ $inspectUser->look }}&direction=2&head_direction=2&size=m" class="h-12 w-12 object-contain bg-black/50 rounded-2xl border border-amber-500/30">
                            <div>
                                <h3 class="text-base font-extrabold text-amber-400">{{ $inspectUser->username }}'s Furniture Manager</h3>
                                <p class="text-xs text-zinc-400">Inspect, alter quantities, or delete furniture from inventory or active rooms.</p>
                            </div>
                        </div>
                        <a href="{{ route('housekeeping.users', ['search' => request('search')]) }}" class="px-4 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-bold text-xs">Close Inspector</a>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                        <!-- INVENTORY / HAND FURNITURE -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-extrabold text-amber-300 uppercase tracking-wider flex items-center justify-between">
                                <span>Hand Inventory</span>
                                <span class="px-2 py-0.5 rounded bg-black/60 text-amber-400 font-mono">{{ $inventoryFurni->count() }} Item Types</span>
                            </h4>

                            <div class="space-y-2 max-h-96 overflow-y-auto pr-2">
                                @forelse($inventoryFurni as $item)
                                    <div class="p-3 rounded-2xl bg-black/50 border border-amber-500/20 flex items-center justify-between text-xs">
                                        <div>
                                            <p class="font-bold text-zinc-200">{{ $item->public_name ?: $item->item_name }}</p>
                                            <p class="text-[10px] text-zinc-500 font-mono">ID: {{ $item->item_id }} (Count: {{ $item->total_count }})</p>
                                        </div>

                                        <form method="POST" action="{{ route('housekeeping.users') }}" class="flex items-center gap-1.5">
                                            @csrf
                                            <input type="hidden" name="action_type" value="update_furniture">
                                            <input type="hidden" name="target_user_id" value="{{ $inspectUser->id }}">
                                            <input type="hidden" name="item_id" value="{{ $item->item_id }}">
                                            <input type="hidden" name="target_location" value="inventory">

                                            <input type="number" name="new_quantity" value="{{ $item->total_count }}" min="0" class="w-16 px-2 py-1 rounded-lg bg-zinc-900 border border-amber-500/30 text-center font-bold text-amber-400 text-xs">
                                            <button type="submit" class="px-3 py-1 rounded-lg bg-amber-500 hover:bg-amber-400 text-zinc-950 font-extrabold text-[10px] uppercase">Update</button>
                                            <button type="submit" name="new_quantity" value="0" onclick="return confirm('Delete all of this item from inventory?')" class="px-2 py-1 rounded-lg bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 font-extrabold text-[10px] uppercase">Delete</button>
                                        </form>
                                    </div>
                                @empty
                                    <p class="text-xs text-zinc-500 italic p-4 text-center">No items found in hand inventory.</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- PLACED ROOM FURNITURE WITH DROPDOWN -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-extrabold text-amber-300 uppercase tracking-wider">Placed Room Furniture</h4>

                                <!-- ROOM SELECT DROPDOWN -->
                                <select x-model="selectedRoom" class="px-3 py-1 rounded-xl bg-black/60 border border-amber-500/30 text-xs font-bold text-amber-400 focus:outline-none">
                                    <option value="all">Show All Rooms</option>
                                    @foreach($roomFurni as $roomId => $items)
                                        <option value="{{ $roomId }}">Room #{{ $roomId }}: {{ $items->first()->room_name ?? 'Guest Room' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                                @forelse($roomFurni as $roomId => $items)
                                    <div x-show="selectedRoom === 'all' || selectedRoom == '{{ $roomId }}'" class="p-3 rounded-2xl bg-black/50 border border-amber-500/20 space-y-2">
                                        <p class="text-xs font-bold text-amber-400 border-b border-amber-500/20 pb-1">Room #{{ $roomId }}: {{ $items->first()->room_name ?? 'Guest Room' }}</p>

                                        @foreach($items as $item)
                                            <div class="flex items-center justify-between text-xs pt-1">
                                                <div>
                                                    <p class="font-semibold text-zinc-300">{{ $item->public_name ?: $item->item_name }}</p>
                                                    <p class="text-[10px] text-zinc-500 font-mono">ID: {{ $item->item_id }} (Count: {{ $item->total_count }})</p>
                                                </div>

                                                <form method="POST" action="{{ route('housekeeping.users') }}" class="flex items-center gap-1.5">
                                                    @csrf
                                                    <input type="hidden" name="action_type" value="update_furniture">
                                                    <input type="hidden" name="target_user_id" value="{{ $inspectUser->id }}">
                                                    <input type="hidden" name="item_id" value="{{ $item->item_id }}">
                                                    <input type="hidden" name="target_location" value="{{ $roomId }}">

                                                    <input type="number" name="new_quantity" value="{{ $item->total_count }}" min="0" class="w-16 px-2 py-1 rounded-lg bg-zinc-900 border border-amber-500/30 text-center font-bold text-amber-400 text-xs">
                                                    <button type="submit" class="px-3 py-1 rounded-lg bg-amber-500 hover:bg-amber-400 text-zinc-950 font-extrabold text-[10px] uppercase">Update</button>
                                                    <button type="submit" name="new_quantity" value="0" onclick="return confirm('Remove all of this item from this room?')" class="px-2 py-1 rounded-lg bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 font-extrabold text-[10px] uppercase">Delete</button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @empty
                                    <p class="text-xs text-zinc-500 italic p-4 text-center">No placed room furniture found.</p>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>
            @endif

            <!-- USERS TABLE WITH COLLAPSIBLE PASSWORD ROW -->
            <div class="bg-zinc-900/90 border border-amber-500/30 rounded-3xl overflow-hidden shadow-xl">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-black/60 border-b border-amber-500/20 text-amber-400 font-extrabold uppercase">
                            <th class="p-4">User</th>
                            <th class="p-4">Motto & Rank</th>
                            <th class="p-4">Credits</th>
                            <th class="p-4">Duckets</th>
                            <th class="p-4">Diamonds</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/80 text-zinc-300">
                        @forelse($users as $user)
                            <tr class="hover:bg-black/30 transition" x-data="{ showPassRow: false }">
                                <td colspan="6" class="p-0">
                                    <form method="POST" action="{{ route('housekeeping.users') }}" class="w-full">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                        <input type="hidden" name="search_back" value="{{ request('search') }}">

                                        <div class="grid grid-cols-12 items-center p-4 gap-2">
                                            <div class="col-span-3 flex items-center gap-3">
                                                <img src="https://www.habbo.com/habbo-imaging/avatarimage?figure={{ $user->look }}&headonly=1" class="h-8 w-8 object-contain bg-black/40 rounded-xl">
                                                <div>
                                                    <p class="font-extrabold text-amber-300">{{ $user->username }}</p>
                                                    <p class="text-[10px] text-zinc-500">{{ $user->mail }}</p>
                                                </div>
                                            </div>

                                            <div class="col-span-3 space-y-1">
                                                <input type="text" name="motto" value="{{ $user->motto }}" class="w-full px-2 py-1 rounded-lg bg-black/50 border border-zinc-700 text-xs">
                                                <div class="flex items-center gap-1">
                                                    <span class="text-[10px] text-zinc-500 uppercase font-bold">Rank:</span>
                                                    <input type="number" name="rank" value="{{ $user->rank }}" class="w-16 px-2 py-0.5 rounded-lg bg-black/50 border border-zinc-700 text-xs font-bold text-amber-400">
                                                </div>
                                            </div>

                                            <div class="col-span-2 font-mono font-bold">
                                                <div class="flex items-center gap-1">
                                                    <img src="https://loungehotel.org/assets/images/icons/credits.png" class="h-4 w-4 object-contain">
                                                    <input type="number" name="credits" value="{{ $user->credits }}" class="w-20 px-2 py-1 rounded-lg bg-black/50 border border-zinc-700 text-xs font-bold text-amber-400">
                                                </div>
                                            </div>

                                            <div class="col-span-1 font-mono font-bold">
                                                <input type="number" name="duckets" value="{{ $user->duckets_amount }}" class="w-16 px-2 py-1 rounded-lg bg-black/50 border border-zinc-700 text-xs font-bold text-purple-400">
                                            </div>

                                            <div class="col-span-1 font-mono font-bold">
                                                <input type="number" name="diamonds" value="{{ $user->diamonds_amount }}" class="w-16 px-2 py-1 rounded-lg bg-black/50 border border-zinc-700 text-xs font-bold text-cyan-400">
                                            </div>

                                            <div class="col-span-2 text-right space-x-1">
                                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-zinc-950 font-black text-xs uppercase tracking-wider transition">Save</button>
                                                <button type="button" @click="showPassRow = !showPassRow" class="px-2 py-1.5 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 font-bold text-xs transition">Password</button>
                                                <a href="{{ route('housekeeping.users', ['inspect_user' => $user->id, 'search' => request('search')]) }}" class="px-2 py-1.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-amber-300 font-bold text-xs transition inline-block">Furni</a>
                                            </div>
                                        </div>

                                        <!-- INLINE EXPANDABLE PASSWORD ROW -->
                                        <div x-show="showPassRow" x-cloak class="px-4 pb-4 pt-1 bg-black/40 border-t border-amber-500/20 flex items-center gap-3">
                                            <span class="text-xs font-bold text-amber-400">New Password for {{ $user->username }}:</span>
                                            <input type="password" name="password" placeholder="Leave empty if unchanged..." class="px-3 py-1.5 rounded-xl bg-zinc-900 border border-amber-500/30 text-xs text-white w-64">
                                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-zinc-950 font-extrabold text-xs uppercase">Update Password</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-zinc-500 italic">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pt-2">
                {{ $users->links() }}
            </div>

        </main>
    </div>
</body>
</html>