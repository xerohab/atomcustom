<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lounge ASE - Chatlogs Auditor</title>
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

            <!-- HEADER & TAB SELECTOR -->
            <div class="bg-zinc-900/90 backdrop-blur-md border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 shadow-2xl flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-amber-400">Chatlogs Auditor</h1>
                    <p class="text-xs text-zinc-300 mt-1">Audit in-game public room dialogue and private whisper/messaging logs across the hotel.</p>
                </div>

                <form method="GET" action="{{ route('housekeeping.chatlogs') }}" class="flex gap-2 w-full md:w-auto">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search messages or usernames..." class="px-3 py-2 rounded-xl bg-black/60 border border-amber-500/30 text-xs font-bold text-white focus:outline-none focus:ring-1 focus:ring-amber-500 w-full md:w-64">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-zinc-950 font-black text-xs uppercase tracking-wider transition">Search</button>
                </form>
            </div>

            <!-- TAB SWITCHER BUTTONS -->
            <div class="flex items-center gap-3 text-xs font-bold">
                <a href="{{ route('housekeeping.chatlogs', ['tab' => 'room', 'search' => $search]) }}" class="px-5 py-2.5 rounded-2xl border transition {{ $tab === 'room' ? 'bg-amber-500 text-zinc-950 border-amber-400 font-extrabold' : 'bg-zinc-900/90 text-zinc-300 border-amber-500/30 hover:border-amber-400/50' }}">
                    Public Room Chatlogs
                </a>
                <a href="{{ route('housekeeping.chatlogs', ['tab' => 'private', 'search' => $search]) }}" class="px-5 py-2.5 rounded-2xl border transition {{ $tab === 'private' ? 'bg-amber-500 text-zinc-950 border-amber-400 font-extrabold' : 'bg-zinc-900/90 text-zinc-300 border-amber-500/30 hover:border-amber-400/50' }}">
                    Private Message Logs
                </a>
            </div>

            <!-- CHATLOGS TABLE -->
            <div class="bg-zinc-900/90 border border-amber-500/30 rounded-3xl overflow-hidden shadow-xl">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-black/60 border-b border-amber-500/20 text-amber-400 font-extrabold uppercase">
                            <th class="p-4">Sender</th>
                            @if($tab === 'room')
                                <th class="p-4">Room Location</th>
                            @else
                                <th class="p-4">Recipient</th>
                            @endif
                            <th class="p-4">Message Log</th>
                            <th class="p-4 text-right">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/80 text-zinc-300">
                        @if($tab === 'room')
                            @forelse($roomLogs as $log)
                                <tr class="hover:bg-black/30 transition">
                                    <td class="p-4 font-bold text-amber-300">
                                        {{ $log->sender_name ?? 'User #' . $log->user_from_id }}
                                    </td>
                                    <td class="p-4 font-semibold text-zinc-400">
                                        Room #{{ $log->room_id }}: <span class="text-white">{{ $log->room_name ?? 'Guest Room' }}</span>
                                    </td>
                                    <td class="p-4 font-mono text-zinc-200">
                                        {{ $log->message }}
                                    </td>
                                    <td class="p-4 text-right font-mono text-zinc-500 text-[11px]">
                                        {{ isset($log->timestamp) ? date('Y-m-d H:i:s', $log->timestamp) : 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-zinc-500 italic">No room chatlogs recorded.</td>
                                </tr>
                            @endforelse
                        @else
                            @forelse($privateLogs as $log)
                                <tr class="hover:bg-black/30 transition">
                                    <td class="p-4 font-bold text-amber-300">
                                        {{ $log->sender_name ?? 'User #' . $log->user_from_id }}
                                    </td>
                                    <td class="p-4 font-bold text-cyan-300">
                                        {{ $log->receiver_name ?? 'User #' . $log->user_to_id }}
                                    </td>
                                    <td class="p-4 font-mono text-zinc-200">
                                        {{ $log->message }}
                                    </td>
                                    <td class="p-4 text-right font-mono text-zinc-500 text-[11px]">
                                        {{ isset($log->timestamp) ? date('Y-m-d H:i:s', $log->timestamp) : 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-zinc-500 italic">No private message logs recorded.</td>
                                </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="pt-2">
                @if($tab === 'room')
                    {{ $roomLogs->links() }}
                @else
                    {{ $privateLogs->links() }}
                @endif
            </div>

        </main>
    </div>
</body>
</html>