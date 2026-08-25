<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lounge Hotel - Open Support Ticket</title>
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
                <div class="flex items-center gap-6">
                    <a href="{{ url('/user/me') }}">
                        <img src="https://loungehotel.org/assets/images/lounge.png" alt="Lounge Logo" class="h-9 w-auto">
                    </a>
                    <nav class="hidden md:flex items-center gap-5 text-xs font-bold uppercase tracking-wider">
                        <a href="{{ route('help-center.index') }}" class="text-amber-400 border-b-2 border-amber-400 pb-1">Help Center</a>
                    </nav>
                </div>
            </div>
        </header>

        <main class="max-w-3xl mx-auto px-4 py-8 space-y-6">

            <div class="bg-zinc-900/90 backdrop-blur-md border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 shadow-2xl space-y-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-amber-400">Open Support Ticket</h1>
                    <p class="text-xs text-zinc-300 mt-1">Select a department and describe your request clearly for staff assistance.</p>
                </div>

                <form method="POST" action="{{ route('help-center.ticket.store') }}" class="space-y-4 text-xs pt-2">
                    @csrf
                    <div>
                        <label class="block font-bold text-amber-300 mb-1">Department</label>
                        <select name="department" required class="w-full px-3 py-2.5 rounded-xl bg-black/60 border border-amber-500/30 font-bold text-amber-400 focus:outline-none">
                            <option value="ban_appeal">Ban Appeal</option>
                            <option value="whitelisting">Whitelisting</option>
                            <option value="general_help" selected>General Help</option>
                            <option value="room_ads_request">Room Ads Request</option>
                            <option value="report_scammer">Report a Scammer</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-amber-300 mb-1">Ticket Subject</label>
                        <input type="text" name="subject" required placeholder="Short summary of your issue..." class="w-full px-3 py-2.5 rounded-xl bg-black/60 border border-amber-500/30 text-white font-bold focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-amber-300 mb-1">Detailed Description</label>
                        <textarea name="message" rows="6" required placeholder="Provide full details, room names, or evidence..." class="w-full px-3 py-2.5 rounded-xl bg-black/60 border border-amber-500/30 text-white font-bold focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <a href="{{ route('help-center.index') }}" class="px-4 py-2 rounded-xl bg-zinc-800 text-zinc-300 font-bold">Cancel</a>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-zinc-950 font-black uppercase tracking-wider transition">Submit Ticket</button>
                    </div>
                </form>
            </div>

        </main>
    </div>
</body>
</html>