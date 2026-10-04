<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solace ASE - Staff Noticeboard</title>
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

        <!-- HEADER -->
        <header class="w-full bg-zinc-950/90 backdrop-blur-md border-b border-amber-500/30 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ url('/housekeeping') }}" class="flex items-center gap-2">
                        <img src="https://solacehotel.pw/assets/images/Solace.png" alt="Solace Logo" class="h-8 w-auto">
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

            <!-- HEADER -->
            <div class="bg-zinc-900/90 backdrop-blur-md border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 shadow-2xl flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-amber-400">Staff Noticeboard</h1>
                    <p class="text-xs text-zinc-300 mt-1">Leave team announcements and important pinned messages for hotel staff.</p>
                </div>
            </div>

            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 font-bold text-xs">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- POST NEW NOTICE FORM -->
                <div class="bg-zinc-900/90 border border-amber-500/30 rounded-3xl p-6 space-y-4 shadow-xl h-fit">
                    <h2 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider border-b border-amber-500/20 pb-2">+ Post Announcement</h2>

                    <form method="POST" action="{{ route('housekeeping.notices') }}" class="space-y-4 text-xs">
                        @csrf
                        <div>
                            <label class="block font-bold text-zinc-400 mb-1">Notice Title</label>
                            <input type="text" name="title" required placeholder="Announcement title..." class="w-full px-3 py-2 rounded-xl bg-black/60 border border-amber-500/30 font-bold text-white focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-400 mb-1">Notice Content</label>
                            <textarea name="content" rows="4" required placeholder="Write your announcement message here..." class="w-full px-3 py-2 rounded-xl bg-black/60 border border-amber-500/30 font-bold text-white focus:outline-none"></textarea>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_pinned" id="is_pinned" class="rounded bg-black border-amber-500/30 text-amber-500">
                            <label for="is_pinned" class="font-bold text-amber-300">Pin to Noticeboard Top & Dashboard</label>
                        </div>

                        <button type="submit" class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-zinc-950 font-black uppercase tracking-wider transition">Publish Notice</button>
                    </form>
                </div>

                <!-- NOTICES LISTING -->
                <div class="lg:col-span-2 space-y-4">
                    @forelse($notices as $notice)
                        <div class="p-5 rounded-3xl border transition shadow-xl {{ $notice->is_pinned ? 'bg-amber-950/30 border-amber-500/60' : 'bg-zinc-900/90 border-amber-500/30' }}">
                            <div class="flex items-center justify-between border-b border-amber-500/20 pb-3 mb-3">
                                <div class="flex items-center gap-3">
                                    <img src="https://www.habbo.com/habbo-imaging/avatarimage?figure={{ $notice->look }}&headonly=1" class="h-8 w-8 object-contain bg-black/50 rounded-xl">
                                    <div>
                                        <h3 class="font-extrabold text-sm text-amber-400 flex items-center gap-2">
                                            <span>{{ $notice->title }}</span>
                                            @if($notice->is_pinned)
                                                <span class="px-2 py-0.5 rounded bg-amber-500 text-zinc-950 text-[10px] font-black uppercase">Pinned</span>
                                            @endif
                                        </h3>
                                        <p class="text-[10px] text-zinc-500">By <strong>{{ $notice->username }}</strong> on {{ date('M d, Y H:i', strtotime($notice->created_at)) }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('housekeeping.notices') }}">
                                        @csrf
                                        <input type="hidden" name="action_type" value="toggle_pin">
                                        <input type="hidden" name="notice_id" value="{{ $notice->id }}">
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-amber-300 font-extrabold text-[10px] uppercase">
                                            {{ $notice->is_pinned ? 'Unpin' : 'Pin' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('housekeeping.notices.delete', $notice->id) }}" onsubmit="return confirm('Delete this notice?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 font-extrabold text-[10px] uppercase">Delete</button>
                                    </form>
                                </div>
                            </div>

                            <p class="text-xs text-zinc-300 leading-relaxed whitespace-pre-line">{{ $notice->content }}</p>
                        </div>
                    @empty
                        <div class="p-8 rounded-3xl bg-zinc-900/90 border border-amber-500/30 text-center text-zinc-500 italic">
                            No notices posted on the noticeboard yet.
                        </div>
                    @endforelse

                    <div>
                        {{ $notices->links() }}
                    </div>
                </div>

            </div>

        </main>
    </div>
</body>
</html>