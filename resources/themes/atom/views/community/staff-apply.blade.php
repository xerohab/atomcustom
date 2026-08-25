<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lounge - Apply for {{ $position->title ?? $position->rank->rank_name ?? 'Staff Position' }}</title>

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #0f0507 url('https://loungehotel.org/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png') no-repeat center center fixed;
            background-size: cover;
        }

        .lounge-card-bg {
            background-image: linear-gradient(to bottom, rgba(15, 5, 7, 0.85), rgba(15, 5, 7, 0.95)),
                              url('https://loungehotel.org/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png');
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

            <a href="{{ route('staff-applications.index') ?? url('/community/staff-applications') }}" class="inline-flex items-center gap-2 text-xs font-bold text-amber-400 hover:underline uppercase tracking-wider">
                &larr; Back to all positions
            </a>

            <!-- POSITION SUMMARY CARD -->
            <div class="bg-zinc-900/90 backdrop-blur-md border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 shadow-2xl flex flex-col md:flex-row items-start justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        @if(!empty($position->rank->badge))
                            <div class="h-12 w-12 rounded-2xl bg-black/60 border border-amber-500/40 p-1 flex items-center justify-center flex-shrink-0 shadow-inner">
                                <img src="/gamedata/c_images/album1584/{{ $position->rank->badge }}.gif" alt="Badge" class="max-h-full max-w-full">
                            </div>
                        @endif
                        <div>
                            <h1 class="text-2xl font-extrabold text-amber-400">
                                Apply for {{ $position->title ?? $position->rank->rank_name ?? 'Position' }}
                            </h1>
                            <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                {{ $position->rank->rank_name ?? 'Staff Team' }}
                            </span>
                        </div>
                    </div>
                    <p class="text-xs text-zinc-300 leading-relaxed pt-2">
                        {{ $position->description }}
                    </p>
                </div>
            </div>

            <!-- APPLICATION FORM CARD -->
            <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 lg:p-8 shadow-xl space-y-6">
                <h2 class="text-sm font-extrabold text-amber-400 uppercase tracking-wide border-b border-amber-500/20 pb-3">
                    Application Questionnaire
                </h2>

                <form action="{{ route('staff-applications.store', $position->id) }}" method="POST" class="space-y-5">
                    @csrf

                    <!-- APPLICANT USER INFO DISPLAY -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-2xl bg-black/50 border border-amber-500/20">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-amber-200/60 mb-1">Applying As</label>
                            <span class="text-xs font-extrabold text-amber-300">{{ auth()->user()->username }}</span>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-amber-200/60 mb-1">Discord Tag / Username</label>
                            <input type="text" name="discord_username" required placeholder="e.g. Username#0000"
                                   class="w-full px-3 py-1.5 rounded-xl bg-zinc-950 border border-amber-500/30 text-xs text-white placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                        </div>
                    </div>

                    <!-- DYNAMIC QUESTIONS / GENERAL QUESTIONS -->
                    @if(isset($position->questions) && count($position->questions) > 0)
                        @foreach($position->questions as $index => $question)
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-amber-300">
                                    {{ $index + 1 }}. {{ $question->question ?? $question }}
                                </label>
                                <textarea name="answers[{{ $question->id ?? $index }}]" rows="4" required
                                          placeholder="Type your response here..."
                                          class="w-full p-4 rounded-xl bg-black/50 border border-amber-500/30 text-white placeholder-zinc-500 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500/50"></textarea>
                            </div>
                        @endforeach
                    @else
                        <!-- DEFAULT APPLICATION TEXT AREA -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-amber-300">
                                Why do you want to join the staff team as a {{ $position->title ?? $position->rank->rank_name }}?
                            </label>
                            <textarea name="content" rows="6" required
                                      placeholder="Detail your prior experience, availability, and why you would be a great fit for Lounge Hotel..."
                                      class="w-full p-4 rounded-xl bg-black/50 border border-amber-500/30 text-white placeholder-zinc-500 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500/50"></textarea>
                        </div>
                    @endif

                    <!-- SUBMIT BUTTON -->
                    <div class="pt-4 border-t border-amber-500/20 flex items-center justify-end">
                        <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 font-bold text-amber-100 text-xs uppercase tracking-wider shadow-lg transition">
                            Submit Application &rarr;
                        </button>
                    </div>
                </form>
            </div>

        </main>
    </div>

</body>
</html>