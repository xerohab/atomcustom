<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lounge ASE - Help Center Tickets</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

                <div class="flex items-center gap-3 text-xs font-bold">
                    <a href="{{ url('/housekeeping') }}" class="text-zinc-300 hover:text-amber-400 transition">&larr; Dashboard</a>
                    <a href="{{ url('/user/me') }}" class="px-3 py-1.5 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 transition">Exit ASE &rarr;</a>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 py-8 space-y-6">

            <div class="bg-zinc-900/90 backdrop-blur-md border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 shadow-2xl flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-amber-400">Help Center Tickets Manager</h1>
                    <p class="text-xs text-zinc-300 mt-1">Audit, claim, and respond to incoming help desk requests filtered by staff department access.</p>
                </div>
            </div>

            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 font-bold text-xs">
                    {{ session('success') }}
                </div>
            @endif

            <!-- INSPECT TICKET DRAWER -->
            @if($inspectTicket)
                <div class="bg-zinc-900/90 backdrop-blur-md border-2 border-amber-500/50 rounded-3xl p-6 space-y-6 shadow-2xl">
                    <div class="flex items-center justify-between border-b border-amber-500/20 pb-4">
                        <div class="flex items-center gap-3">
                            <img src="https://www.habbo.com/habbo-imaging/avatarimage?figure={{ $inspectTicket->author_look }}&headonly=1" class="h-10 w-10 object-contain bg-black/50 rounded-2xl border border-amber-500/30">
                            <div>
                                <h3 class="text-base font-extrabold text-amber-400">Ticket #{{ $inspectTicket->id }}: {{ $inspectTicket->subject }}</h3>
                                <p class="text-xs text-zinc-400">Submitted by <strong>{{ $inspectTicket->author_name }}</strong> in <strong>{{ str_replace('_', ' ', strtoupper($inspectTicket->department)) }}</strong></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if(!$inspectTicket->assigned_to)
                                <form method="POST" action="{{ route('housekeeping.tickets') }}">
                                    @csrf
                                    <input type="hidden" name="ticket_id" value="{{ $inspectTicket->id }}">
                                    <button type="submit" name="action_claim" class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-zinc-950 font-black text-xs uppercase">Claim Ticket</button>
                                </form>
                            @else
                                <span class="text-xs font-bold text-zinc-400">Claimed by: <strong class="text-amber-300">{{ $inspectTicket->staff_name }}</strong></span>
                            @endif

                            <a href="{{ route('housekeeping.tickets') }}" class="px-3 py-1.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-bold text-xs">Close Drawer</a>
                        </div>
                    </div>

                    <!-- ORIGINAL TICKET BODY -->
                    <div class="p-4 rounded-2xl bg-black/50 border border-amber-500/20 text-xs space-y-2">
                        <span class="text-[10px] text-amber-400 font-extrabold uppercase">Original Issue Description</span>
                        <p class="text-zinc-200 leading-relaxed whitespace-pre-line">{{ $inspectTicket->message }}</p>
                    </div>

                    <!-- CONVERSATION THREAD -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider">Conversation Thread</h4>
                        <div class="space-y-2 max-h-80 overflow-y-auto pr-2">
                            @forelse($inspectReplies as $reply)
                                <div class="p-3 rounded-2xl border text-xs space-y-1 {{ $reply->rank >= 6 ? 'bg-amber-950/20 border-amber-500/40 ml-6' : 'bg-black/50 border-zinc-800 mr-6' }}">
                                    <div class="flex items-center justify-between">
                                        <span class="font-extrabold {{ $reply->rank >= 6 ? 'text-amber-400' : 'text-zinc-200' }}">{{ $reply->username }}</span>
                                        <span class="text-[10px] font-mono text-zinc-500">{{ $reply->created_at }}</span>
                                    </div>
                                    <p class="text-zinc-300 whitespace-pre-line">{{ $reply->message }}</p>
                                </div>
                            @empty
                                <p class="text-xs text-zinc-500 italic p-2">No replies yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- STAFF REPLY FORM -->
                    <form method="POST" action="{{ route('housekeeping.tickets') }}" class="space-y-3 pt-2 border-t border-amber-500/20">
                        @csrf
                        <input type="hidden" name="ticket_id" value="{{ $inspectTicket->id }}">
                        <textarea name="reply_message" required rows="3" placeholder="Type your official staff response..." class="w-full px-3 py-2 rounded-2xl bg-black/60 border border-amber-500/30 text-xs font-bold text-white focus:outline-none"></textarea>

                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="font-bold text-zinc-400">Change Status:</span>
                                <button type="submit" name="action_status" value="1" onclick="document.getElementsByName('status')[0].value='closed'" class="px-2.5 py-1 rounded-xl bg-red-950 hover:bg-red-900 border border-red-500/40 text-amber-300 font-extrabold text-[10px] uppercase">Close Ticket</button>
                                <input type="hidden" name="status" value="closed">
                            </div>

                            <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-zinc-950 font-black text-xs uppercase tracking-wider">Send Response</button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- TICKETS TABLE -->
            <div class="bg-zinc-900/90 border border-amber-500/30 rounded-3xl overflow-hidden shadow-xl">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-black/60 border-b border-amber-500/20 text-amber-400 font-extrabold uppercase">
                            <th class="p-4">Ticket #</th>
                            <th class="p-4">Submitter</th>
                            <th class="p-4">Department</th>
                            <th class="p-4">Subject</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Assigned To</th>
                            <th class="p-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/80 text-zinc-300">
                        @forelse($tickets as $ticket)
                            <tr class="hover:bg-black/30 transition">
                                <td class="p-4 font-mono font-extrabold text-amber-400">#{{ $ticket->id }}</td>
                                <td class="p-4 font-bold text-white">{{ $ticket->author_name }}</td>
                                <td class="p-4 uppercase font-bold text-[10px] text-amber-300">{{ str_replace('_', ' ', $ticket->department) }}</td>
                                <td class="p-4 font-semibold text-zinc-200">{{ $ticket->subject }}</td>
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded-lg text-[10px] font-extrabold uppercase {{ $ticket->status === 'open' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : ($ticket->status === 'in_progress' ? 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30' : 'bg-red-500/20 text-red-400 border border-red-500/30') }}">
                                        {{ str_replace('_', ' ', $ticket->status) }}
                                    </span>
                                </td>
                                <td class="p-4 font-bold text-zinc-400">{{ $ticket->staff_name ?? 'Unassigned' }}</td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('housekeeping.tickets', ['inspect' => $ticket->id]) }}" class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-zinc-950 font-black text-xs uppercase tracking-wider transition inline-block">Manage</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-zinc-500 italic">No tickets found for your assigned department permissions.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pt-2">
                {{ $tickets->links() }}
            </div>

        </main>
    </div>
</body>
</html>