<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solace Hotel - Ticket #{{ $ticket->id }}</title>
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
                <div class="flex items-center gap-6">
                    <a href="{{ url('/user/me') }}">
                        <img src="https://solacehotel.pw/assets/images/Solace.png" alt="Solace Logo" class="h-9 w-auto">
                    </a>
                    <nav class="hidden md:flex items-center gap-5 text-xs font-bold uppercase tracking-wider">
                        <a href="{{ route('help-center.ticket.index') }}" class="text-zinc-300 hover:text-amber-300 transition">&larr; Back to Tickets</a>
                    </nav>
                </div>
            </div>
        </header>

        <main class="max-w-4xl mx-auto px-4 py-8 space-y-6">

            <div class="bg-zinc-900/90 backdrop-blur-md border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-amber-500/20 pb-4">
                    <div>
                        <h1 class="text-xl font-extrabold text-amber-400">Ticket #{{ $ticket->id }}: {{ $ticket->subject }}</h1>
                        <p class="text-xs text-zinc-400">Department: <strong class="text-amber-300 uppercase">{{ str_replace('_', ' ', $ticket->department) }}</strong></p>
                    </div>
                    <span class="px-3 py-1 rounded-xl text-xs font-extrabold uppercase {{ $ticket->status === 'open' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' }}">
                        {{ $ticket->status }}
                    </span>
                </div>

                <div class="p-4 rounded-2xl bg-black/50 border border-amber-500/20 text-xs space-y-1">
                    <span class="text-[10px] text-amber-400 font-extrabold uppercase">Original Message</span>
                    <p class="text-zinc-200 leading-relaxed whitespace-pre-line">{{ $ticket->message }}</p>
                </div>

                <div class="space-y-3 pt-2">
                    <h3 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider">Conversation History</h3>

                    <div class="space-y-3 max-h-96 overflow-y-auto pr-2">
                        @forelse($replies as $reply)
                            <div class="p-3.5 rounded-2xl border text-xs space-y-1 {{ $reply->rank >= 6 ? 'bg-amber-950/20 border-amber-500/40 ml-6' : 'bg-black/50 border-zinc-800 mr-6' }}">
                                <div class="flex items-center justify-between">
                                    <span class="font-extrabold {{ $reply->rank >= 6 ? 'text-amber-400' : 'text-zinc-200' }}">{{ $reply->username }} {{ $reply->rank >= 6 ? '(Staff)' : '' }}</span>
                                    <span class="text-[10px] font-mono text-zinc-500">{{ date('M d, Y H:i', strtotime($reply->created_at)) }}</span>
                                </div>
                                <p class="text-zinc-300 whitespace-pre-line leading-relaxed">{{ $reply->message }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-zinc-500 italic p-2">No replies yet. Staff will respond shortly.</p>
                        @endforelse
                    </div>
                </div>

                @if($ticket->status !== 'closed')
                    <form method="POST" action="{{ route('help-center.ticket.reply.store', $ticket->id) }}" class="pt-4 border-t border-amber-500/20 space-y-3">
                        @csrf
                        <textarea name="message" rows="3" required placeholder="Type your reply..." class="w-full px-3 py-2 rounded-2xl bg-black/60 border border-amber-500/30 text-xs text-white font-bold focus:outline-none"></textarea>
                        <div class="flex justify-end">
                            <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-zinc-950 font-black text-xs uppercase tracking-wider transition">Send Reply</button>
                        </div>
                    </form>
                @endif
            </div>

        </main>
    </div>
</body>
</html>