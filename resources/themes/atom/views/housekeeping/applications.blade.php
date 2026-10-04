<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solace ASE - Staff Applications</title>

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

            <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 lg:p-8 shadow-xl space-y-6">
                <div class="border-b border-amber-500/20 pb-4 flex justify-between items-center">
                    <div>
                        <h1 class="text-xl font-extrabold text-amber-400 uppercase tracking-wide">Staff Applications</h1>
                        <p class="text-xs text-zinc-400 mt-0.5">Review, accept, reject, or delete submitted user applications.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-amber-500/20 text-amber-300 uppercase tracking-wider text-[10px]">
                                <th class="pb-3 px-2">ID</th>
                                <th class="pb-3 px-2">Applicant</th>
                                <th class="pb-3 px-2">Target Rank / Team</th>
                                <th class="pb-3 px-2">Status</th>
                                <th class="pb-3 px-2">Submitted Date</th>
                                <th class="pb-3 px-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-500/10 text-zinc-300">
                            @forelse($applications as $app)
                                <tr class="hover:bg-black/30 transition">
                                    <td class="py-3 px-2 font-mono text-zinc-500">#{{ $app->id }}</td>
                                    <td class="py-3 px-2 font-bold text-white">{{ $app->applicant_name }}</td>
                                    <td class="py-3 px-2 text-zinc-300">
                                        @if($app->rank_id) Rank #{{ $app->rank_id }} @endif
                                        @if($app->team_id) Team #{{ $app->team_id }} @endif
                                    </td>
                                    <td class="py-3 px-2 font-bold uppercase text-[10px]">
                                        @if($app->status === 'accepted')
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-950 border border-emerald-500/40 text-emerald-400">Accepted</span>
                                        @elseif($app->status === 'rejected')
                                            <span class="px-2 py-0.5 rounded-full bg-red-950 border border-red-500/40 text-red-400">Rejected</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full bg-amber-950 border border-amber-500/40 text-amber-400">Pending</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-2 text-zinc-500">{{ $app->created_at }}</td>
                                    <td class="py-3 px-2 text-right flex justify-end gap-1.5">
                                        <button type="button" onclick='openViewApplicationModal(@json($app))' class="px-2.5 py-1 rounded-lg bg-black/60 border border-amber-500/30 text-amber-300 font-bold hover:bg-amber-500/20 transition">Read</button>

                                        @if($app->status === 'pending')
                                            <form action="{{ url('/housekeeping/applications/' . $app->id . '/accept') }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-950 border border-emerald-500/40 text-emerald-300 font-bold hover:bg-emerald-900 transition">Accept</button>
                                            </form>
                                            <form action="{{ url('/housekeeping/applications/' . $app->id . '/reject') }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-amber-950 border border-amber-500/40 text-amber-300 font-bold hover:bg-amber-900 transition">Reject</button>
                                            </form>
                                        @endif

                                        <form action="{{ url('/housekeeping/applications/' . $app->id) }}" method="POST" onsubmit="return confirm('Delete this application?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-red-950 border border-red-500/40 text-red-300 font-bold hover:bg-red-900 transition">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-zinc-500 italic">No applications submitted yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-4 border-t border-amber-500/20">
                    {{ $applications->links() }}
                </div>
            </div>
        </main>
    </div>

    <!-- VIEW APPLICATION MODAL -->
    <div id="viewAppModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-zinc-900 border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 max-w-xl w-full space-y-4 relative shadow-2xl">
            <div class="flex justify-between items-center border-b border-amber-500/20 pb-3">
                <h3 id="modal_app_title" class="text-base font-extrabold text-amber-400 uppercase tracking-wide">Application Details</h3>
                <button type="button" onclick="closeViewApplicationModal()" class="text-zinc-400 hover:text-white font-bold">&times;</button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="p-4 rounded-2xl bg-black/50 border border-amber-500/20 whitespace-pre-wrap leading-relaxed text-zinc-200" id="modal_app_content"></div>
            </div>

            <div class="pt-3 border-t border-amber-500/20 flex justify-end">
                <button type="button" onclick="closeViewApplicationModal()" class="px-5 py-2 rounded-xl bg-black/60 border border-zinc-700 text-zinc-300 font-bold">Close</button>
            </div>
        </div>
    </div>

    <script>
        function openViewApplicationModal(app) {
            document.getElementById('modal_app_title').innerText = 'Application from ' + app.applicant_name;
            document.getElementById('modal_app_content').innerText = app.content || 'No content provided.';
            document.getElementById('viewAppModal').classList.remove('hidden');
        }

        function closeViewApplicationModal() {
            document.getElementById('viewAppModal').classList.add('hidden');
        }
    </script>
</body>
</html>