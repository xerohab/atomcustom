<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solace - Session Logs</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: {
                            300: '#fde047',
                            400: '#facc15',
                            500: '#eab308',
                            600: '#ca8a04',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #0f0507 url('https://solacehotel.pw/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png') no-repeat center center fixed;
            background-size: cover;
        }

        .solace-card-bg {
            background-image: linear-gradient(to bottom, rgba(15, 5, 7, 0.85), rgba(15, 5, 7, 0.95)),
                              url('https://solacehotel.pw/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body class="min-h-screen text-white relative overflow-x-hidden antialiased flex flex-col justify-between">

    <!-- Dark Page Overlay -->
    <div class="fixed inset-0 bg-black/60 backdrop-blur-[2px] z-0"></div>

    <!-- Content Wrapper -->
    <div class="relative z-10">

        <!-- UNIVERSAL NAVIGATION MENU -->
        @include('components.navigation.navigation-menu')

        <!-- MAIN CONTAINER -->
        <main class="max-w-4xl mx-auto px-4 py-8 space-y-6">

            <!-- HEADER HERO -->
            <div class="bg-zinc-900/90 backdrop-blur-md border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 shadow-2xl flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold text-amber-400">Settings</h1>
                    <p class="text-xs text-zinc-300 mt-1">
                        Manage your account preferences, motto, email address, and security.
                    </p>
                </div>
            </div>

            <!-- SETTINGS SUB-NAVIGATION -->
            <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-2xl p-2 flex flex-wrap gap-2">
                <a href="{{ url('/user/settings/account') }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ Request::is('user/settings/account*') ? 'bg-amber-500 text-black shadow-md' : 'text-zinc-300 hover:text-amber-400 hover:bg-black/40' }}">
                    Account
                </a>
                <a href="{{ url('/user/settings/password') }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ Request::is('user/settings/password*') ? 'bg-amber-500 text-black shadow-md' : 'text-zinc-300 hover:text-amber-400 hover:bg-black/40' }}">
                    Password
                </a>
                <a href="{{ url('/user/settings/session-logs') }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ Request::is('user/settings/session-logs*') ? 'bg-amber-500 text-black shadow-md' : 'text-zinc-300 hover:text-amber-400 hover:bg-black/40' }}">
                    Session Logs
                </a>
                <a href="{{ url('/user/settings/two-factor') }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ Request::is('user/settings/two-factor*') ? 'bg-amber-500 text-black shadow-md' : 'text-zinc-300 hover:text-amber-400 hover:bg-black/40' }}">
                    Two-Factor Auth
                </a>
            </div>

            <!-- SESSION LOGS CARD -->
            <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 lg:p-8 shadow-xl space-y-6">
                <div class="flex justify-between items-center border-b border-amber-500/20 pb-3">
                    <h2 class="text-sm font-extrabold text-amber-400 uppercase tracking-wide">
                        Recent Logins & Activity
                    </h2>
                    <span class="text-xs text-zinc-400">Security Audit History</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-amber-500/20 text-amber-300 uppercase tracking-wider text-[10px]">
                                <th class="pb-3 px-2">IP Address</th>
                                <th class="pb-3 px-2">Action / Status</th>
                                <th class="pb-3 px-2">Date & Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-500/10 text-zinc-300">
                            @forelse($logs ?? [] as $log)
                                <tr class="hover:bg-black/30 transition">
                                    <td class="py-3 px-2 font-mono font-bold text-amber-200">
                                        {{ $log->ip ?? $log->ip_address ?? 'Hidden' }}
                                    </td>
                                    <td class="py-3 px-2">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-black/50 border border-amber-500/30 text-amber-400">
                                            {{ $log->action ?? $log->message ?? 'Login Attempt' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-2 text-zinc-400">
                                        {{ date('d M Y, H:i', $log->timestamp ?? strtotime($log->created_at ?? 'now')) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-zinc-500 italic">
                                        No recent session logs recorded.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION LINKS (IF AVAILABLE) -->
                @if(isset($logs) && method_exists($logs, 'links'))
                    <div class="pt-4 border-t border-amber-500/20">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>

        </main>
    </div>

</body>
</html>