<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solace - {{ auth()->user()->username }}</title>

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

    <!-- Google Fonts & Swiper CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        <main class="max-w-7xl mx-auto px-3 sm:px-4 py-4 sm:py-8 space-y-4 sm:space-y-6">

            <!-- TOP HERO SECTION (PROFILE & NEWS SLIDER) -->
            <div class="grid grid-cols-12 gap-4 sm:gap-6 items-stretch">

                <!-- LEFT PROFILE HERO CARD -->
                <div class="col-span-12 lg:col-span-7 bg-zinc-900/90 backdrop-blur-md border-2 border-amber-500/40 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-2xl flex flex-col justify-between">
                    <div>
                        <div class="flex flex-col min-[430px]:flex-row min-[430px]:items-center gap-3 sm:gap-4">
                            <div class="h-24 w-20 sm:h-28 sm:w-24 flex items-center justify-center rounded-2xl bg-black/60 border border-amber-500/40 shadow-inner p-2 relative overflow-hidden flex-shrink-0">
                                <img src="{{ setting('avatar_imager') }}{{ auth()->user()->look }}&direction=2&head_direction=3&gesture=sml&action=wav&size=l"
                                     alt="Avatar" class="-mt-4 drop-shadow-lg" style="image-rendering: pixelated;">
                            </div>

                            <div class="space-y-1 w-full">
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                                    <span class="text-xs font-bold uppercase tracking-wider text-amber-200/60">Welcome back,</span>

                                    <!-- EDIT PROFILE / DISCORD LINK BUTTON -->
                                    <a href="{{ route('settings.account.show') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/40 text-amber-400 font-extrabold text-[11px] uppercase tracking-wider transition transform active:scale-95">
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 127.14 96.36">
                                            <path d="M107.7,8.07A105.15,105.15,0,0,0,81.47,0a72.06,72.06,0,0,0-3.36,6.83A97.68,97.68,0,0,0,49,6.83,72.37,72.37,0,0,0,45.64,0,105.89,105.89,0,0,0,19.39,8.09C2.79,32.65-1.71,56.6.54,80.21h0A105.73,105.73,0,0,0,32.71,96.36,77.7,77.7,0,0,0,39.6,85.25a68.42,68.42,0,0,1-10.85-5.18c.91-.66,1.8-1.34,2.66-2a75.57,75.57,0,0,0,64.32,0c.87.71,1.76,1.39,2.66,2a68.68,68.68,0,0,1-10.87,5.19,77,77,0,0,0,6.89,11.1A105.25,105.25,0,0,0,126.6,80.22h0C129.24,52.84,122.09,29.11,107.7,8.07ZM42.45,65.69C36.18,65.69,31,60,31,53s5-12.74,11.43-12.74S54,45.92,53.86,53,48.83,65.69,42.45,65.69Zm42.24,0C78.41,65.69,73.25,60,73.25,53s5-12.74,11.44-12.74S96.23,45.92,96.09,53,91.08,65.69,84.69,65.69Z"/>
                                        </svg>
                                        <span>Edit Profile And Link Discord</span>
                                    </a>
                                </div>

                                <h1 class="text-2xl sm:text-3xl font-extrabold text-amber-400 break-words">{{ auth()->user()->username }}</h1>

                                <!-- Motto Display Box -->
                                <div class="pt-1">
                                    <div class="bg-black/50 border border-amber-500/30 rounded-xl px-3 py-1.5 text-xs text-zinc-300 italic w-full">
                                        {{ auth()->user()->motto ?? 'No motto set' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- STATS GRID (Joined, Respects, Achievement Score) -->
                        <div class="grid grid-cols-1 min-[430px]:grid-cols-3 gap-2 sm:gap-3 mt-4 sm:mt-6">
                            <div class="bg-black/50 border border-amber-500/30 rounded-2xl p-3 text-center">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-amber-200/60">Joined</span>
                                <span class="text-xs font-bold text-white mt-1 block">
                                    {{ auth()->user()->account_created > 0 ? date('d M Y', auth()->user()->account_created) : date('d M Y', strtotime(auth()->user()->created_at ?? 'now')) }}
                                </span>
                            </div>
                            <div class="bg-black/50 border border-amber-500/30 rounded-2xl p-3 text-center">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-amber-200/60">Respects</span>
                                <span class="text-xs font-bold text-amber-400 mt-1 block">
                                    {{ number_format(auth()->user()->settings->respects_received ?? 0) }} received
                                </span>
                            </div>
                            <div class="bg-black/50 border border-amber-500/30 rounded-2xl p-3 text-center">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-amber-200/60">Achievement</span>
                                <span class="text-xs font-bold text-amber-400 mt-1 block">
                                    {{ number_format(auth()->user()->settings->achievement_score ?? 0) }} pts
                                </span>
                            </div>
                        </div>

                        <!-- CURRENCIES ROW (Credits, Duckets, Diamonds) -->
                        <div class="grid grid-cols-1 min-[430px]:grid-cols-3 gap-2 sm:gap-3 mt-4">
                            <!-- Credits -->
                            <div class="bg-red-950/60 border border-amber-500/40 rounded-2xl p-3 flex items-center gap-3 min-w-0">
                                <img src="https://solacehotel.pw/assets/images/profile/credits.png" alt="Credits" class="h-6 w-auto">
                                <div>
                                    <div class="text-sm font-black text-amber-400">{{ number_format(auth()->user()->credits ?? 0) }}</div>
                                    <div class="text-[10px] font-bold uppercase text-amber-200/60">Credits</div>
                                </div>
                            </div>

                            <!-- Duckets / Pixels -->
                            <div class="bg-red-950/60 border border-amber-500/40 rounded-2xl p-3 flex items-center gap-3 min-w-0">
                                <img src="https://solacehotel.pw/assets/images/profile/duckets.png" alt="Duckets" class="h-6 w-auto">
                                <div>
                                    <div class="text-sm font-black text-amber-400">{{ number_format(auth()->user()->currency('duckets') ?? 0) }}</div>
                                    <div class="text-[10px] font-bold uppercase text-amber-200/60">Duckets</div>
                                </div>
                            </div>

                            <!-- Diamonds -->
                            <div class="bg-red-950/60 border border-amber-500/40 rounded-2xl p-3 flex items-center gap-3 min-w-0">
                                <img src="https://solacehotel.pw/assets/images/profile/diamonds.png" alt="Diamonds" class="h-6 w-auto">
                                <div>
                                    <div class="text-sm font-black text-amber-400">
                                        {{ number_format(auth()->user()->currency('diamonds') ?? 0) }}
                                    </div>
                                    <div class="text-[10px] font-bold uppercase text-amber-200/60">Diamonds</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CLIENT LAUNCH OPTIONS -->
                    <div class="mt-4">
                        <div class="mb-3">
                            <div class="text-xs sm:text-sm font-black uppercase tracking-wider text-amber-400">Choose how you want to play</div>
                            <div class="text-[10px] sm:text-xs text-amber-100/60 mt-1">Pick your device and the hotel server you want to connect to.</div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                            <!-- Desktop UK -->
                            <a data-turbolinks="false"
                               href="{{ route('nitro-client') }}?region=uk"
                               data-client-device="desktop"
                               class="w-full group js-client-device-guard">
                                <div class="w-full min-h-[62px] px-4 py-3 rounded-2xl bg-gradient-to-r from-blue-900 to-blue-700 hover:from-blue-800 hover:to-blue-600 border border-blue-400/50 text-white shadow-lg transform active:scale-95 transition flex items-center gap-3">
                                    <svg class="h-5 w-7 shrink-0 rounded-sm shadow-sm" viewBox="0 0 60 36" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><rect width="60" height="36" fill="#012169"/><path d="M0 0L60 36M60 0L0 36" stroke="#fff" stroke-width="7"/><path d="M0 0L60 36M60 0L0 36" stroke="#C8102E" stroke-width="4"/><path d="M30 0v36M0 18h60" stroke="#fff" stroke-width="12"/><path d="M30 0v36M0 18h60" stroke="#C8102E" stroke-width="7"/></svg>
                                    <span class="min-w-0 text-left">
                                        <span class="block text-xs sm:text-sm font-black uppercase tracking-wide leading-tight">Play On Computer In UK</span>
                                    </span>
                                    <span class="ml-auto text-base opacity-80">&rarr;</span>
                                </div>
                            </a>

                            <!-- Desktop USA -->
                            <a data-turbolinks="false"
                               href="{{ route('nitro-client') }}?region=usa"
                               data-client-device="desktop"
                               class="w-full group js-client-device-guard">
                                <div class="w-full min-h-[62px] px-4 py-3 rounded-2xl bg-gradient-to-r from-red-900 via-red-800 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/50 text-amber-50 shadow-lg transform active:scale-95 transition flex items-center gap-3">
                                    <svg class="h-5 w-7 shrink-0 rounded-sm shadow-sm" viewBox="0 0 60 36" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><rect width="60" height="36" fill="#fff"/><g fill="#B22234"><rect y="0" width="60" height="3"/><rect y="6" width="60" height="3"/><rect y="12" width="60" height="3"/><rect y="18" width="60" height="3"/><rect y="24" width="60" height="3"/><rect y="30" width="60" height="3"/><rect y="33" width="60" height="3"/></g><rect width="27" height="19" fill="#3C3B6E"/><g fill="#fff"><circle cx="3" cy="3" r="1"/><circle cx="8" cy="3" r="1"/><circle cx="13" cy="3" r="1"/><circle cx="18" cy="3" r="1"/><circle cx="23" cy="3" r="1"/><circle cx="5.5" cy="7" r="1"/><circle cx="10.5" cy="7" r="1"/><circle cx="15.5" cy="7" r="1"/><circle cx="20.5" cy="7" r="1"/><circle cx="3" cy="11" r="1"/><circle cx="8" cy="11" r="1"/><circle cx="13" cy="11" r="1"/><circle cx="18" cy="11" r="1"/><circle cx="23" cy="11" r="1"/><circle cx="5.5" cy="15" r="1"/><circle cx="10.5" cy="15" r="1"/><circle cx="15.5" cy="15" r="1"/><circle cx="20.5" cy="15" r="1"/></g></svg>
                                    <span class="min-w-0 text-left">
                                        <span class="block text-xs sm:text-sm font-black uppercase tracking-wide leading-tight">Play On Computer In USA</span>
                                    </span>
                                    <span class="ml-auto text-base opacity-80">&rarr;</span>
                                </div>
                            </a>

                            <!-- Mobile UK -->
                            <a data-turbolinks="false"
                               href="{{ route('nitro-client') }}?region=uk&mobile=1"
                               data-client-device="mobile"
                               class="w-full group js-client-device-guard">
                                <div class="w-full min-h-[62px] px-4 py-3 rounded-2xl bg-gradient-to-r from-cyan-900 to-emerald-700 hover:from-cyan-800 hover:to-emerald-600 border border-cyan-400/50 text-white shadow-lg transform active:scale-95 transition flex items-center gap-3">
                                    <svg class="h-5 w-7 shrink-0 rounded-sm shadow-sm" viewBox="0 0 60 36" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><rect width="60" height="36" fill="#012169"/><path d="M0 0L60 36M60 0L0 36" stroke="#fff" stroke-width="7"/><path d="M0 0L60 36M60 0L0 36" stroke="#C8102E" stroke-width="4"/><path d="M30 0v36M0 18h60" stroke="#fff" stroke-width="12"/><path d="M30 0v36M0 18h60" stroke="#C8102E" stroke-width="7"/></svg>
                                    <span class="min-w-0 text-left">
                                        <span class="block text-xs sm:text-sm font-black uppercase tracking-wide leading-tight">Play On Phone In UK</span>
                                    </span>
                                    <span class="ml-auto text-base opacity-80">&rarr;</span>
                                </div>
                            </a>

                            <!-- Mobile USA -->
                            <a data-turbolinks="false"
                               href="{{ route('nitro-client') }}?region=usa&mobile=1"
                               data-client-device="mobile"
                               class="w-full group js-client-device-guard">
                                <div class="w-full min-h-[62px] px-4 py-3 rounded-2xl bg-gradient-to-r from-purple-900 to-pink-700 hover:from-purple-800 hover:to-pink-600 border border-pink-400/50 text-white shadow-lg transform active:scale-95 transition flex items-center gap-3">
                                    <svg class="h-5 w-7 shrink-0 rounded-sm shadow-sm" viewBox="0 0 60 36" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><rect width="60" height="36" fill="#fff"/><g fill="#B22234"><rect y="0" width="60" height="3"/><rect y="6" width="60" height="3"/><rect y="12" width="60" height="3"/><rect y="18" width="60" height="3"/><rect y="24" width="60" height="3"/><rect y="30" width="60" height="3"/><rect y="33" width="60" height="3"/></g><rect width="27" height="19" fill="#3C3B6E"/><g fill="#fff"><circle cx="3" cy="3" r="1"/><circle cx="8" cy="3" r="1"/><circle cx="13" cy="3" r="1"/><circle cx="18" cy="3" r="1"/><circle cx="23" cy="3" r="1"/><circle cx="5.5" cy="7" r="1"/><circle cx="10.5" cy="7" r="1"/><circle cx="15.5" cy="7" r="1"/><circle cx="20.5" cy="7" r="1"/><circle cx="3" cy="11" r="1"/><circle cx="8" cy="11" r="1"/><circle cx="13" cy="11" r="1"/><circle cx="18" cy="11" r="1"/><circle cx="23" cy="11" r="1"/><circle cx="5.5" cy="15" r="1"/><circle cx="10.5" cy="15" r="1"/><circle cx="15.5" cy="15" r="1"/><circle cx="20.5" cy="15" r="1"/></g></svg>
                                    <span class="min-w-0 text-left">
                                        <span class="block text-xs sm:text-sm font-black uppercase tracking-wide leading-tight">Play On Phone In USA</span>
                                    </span>
                                    <span class="ml-auto text-base opacity-80">&rarr;</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- RIGHT NEWS SLIDER CARD -->
                <div class="col-span-12 lg:col-span-5 bg-zinc-900/90 backdrop-blur-md border-2 border-amber-500/40 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-2xl flex flex-col justify-between">
                    <div class="flex items-center justify-between border-b border-amber-500/20 pb-3 mb-4">
                        <h2 class="text-sm font-extrabold text-amber-400 uppercase tracking-wide">Latest News</h2>
                        <a href="{{ url('/community/articles') }}" class="text-xs font-bold text-amber-400 hover:underline">All news &rarr;</a>
                    </div>

                    <div class="swiper articles-slider rounded-2xl overflow-hidden h-full min-h-[300px]">
                        <div class="swiper-wrapper">
                            @forelse ($articles as $article)
                                <div class="swiper-slide relative rounded-2xl overflow-hidden bg-cover bg-center border border-amber-500/20 p-6 flex flex-col justify-between"
                                     style="background-image: linear-gradient(to top, rgba(0,0,0,0.95), rgba(0,0,0,0.3)), url('{{ $article->image }}');">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-amber-400 bg-black/80 border border-amber-500/40 px-3 py-1 rounded-full w-max">
                                        News Release
                                    </span>
                                    <div>
                                        <h3 class="text-xl font-bold text-white line-clamp-1">{{ $article->title }}</h3>
                                        <p class="text-xs text-zinc-300 line-clamp-3 mt-1">{{ $article->short_story }}</p>
                                        <a href="{{ route('articles.show', $article->slug) }}" class="inline-block text-xs font-bold text-amber-400 hover:underline mt-3">
                                            Read article &rarr;
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-xs text-zinc-400 italic">No news articles published.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>

            <!-- BOTTOM SECTION: ONLINE FRIENDS & REFERRALS -->
            <div class="grid grid-cols-12 gap-4 sm:gap-6">

                <!-- ONLINE FRIENDS -->
                <div class="col-span-12 lg:col-span-6 bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-xl">
                    <h2 class="text-sm font-extrabold text-amber-400 uppercase tracking-wide border-b border-amber-500/20 pb-3 mb-4">
                        Online Friends ({{ count($onlineFriends ?? []) }})
                    </h2>

                    <div class="flex flex-wrap gap-3 items-center min-h-[60px]">
                        @forelse ($onlineFriends ?? [] as $friend)
                            <div title="{{ $friend->username }} - Motto: {{ $friend->motto ?? 'No motto set' }}"
                                 style="image-rendering: pixelated; background-image: url({{ setting('avatar_imager') }}{{ $friend->look }}&direction=2&head_direction=3&gesture=sml&action=wav&headonly=1&size=s)"
                                 class="h-10 w-10 rounded-full border-2 border-amber-500/40 bg-black/60 bg-center bg-no-repeat cursor-pointer hover:scale-110 transition">
                            </div>
                        @empty
                            <div class="text-center w-full py-4">
                                <p class="text-xs text-zinc-400 italic">No friends online right now.</p>
                                <span class="text-[11px] text-zinc-500 mt-0.5 block">Your online friends will appear here.</span>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- REFERRALS -->
                <div class="col-span-12 lg:col-span-6 bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-xl space-y-3">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 border-b border-amber-500/20 pb-3">
                        <h2 class="text-sm font-extrabold text-amber-400 uppercase tracking-wide">
                            Invite friends. Earn rewards!
                        </h2>
                        <span class="text-xs font-bold text-amber-300">
                            {{ auth()->user()->referrals->referrals_total ?? 0 }}/{{ setting('referrals_needed') }} Referrals
                        </span>
                    </div>

                    <p class="text-xs text-zinc-300 leading-relaxed">
                        For every {{ setting('referrals_needed') }} users that join through your link, you'll earn {{ setting('referral_reward_amount') }} diamonds!
                    </p>

                    <div class="flex flex-col sm:flex-row gap-2 pt-2">
                        <input id="referral" type="text" readonly
                               value="{{ sprintf('%s/register/%s', config('habbo.site.site_url'), auth()->user()->referral_code) }}"
                               class="w-full px-4 py-2.5 rounded-xl bg-black/50 border border-amber-500/30 text-amber-200 text-xs focus:outline-none">
                        <button type="button" onclick="copyCode()" class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-black font-extrabold text-xs uppercase tracking-wider transition whitespace-nowrap">
                            Copy Link
                        </button>
                    </div>

                    @if (auth()->user()->referrals?->referrals_total >= (int) setting('referrals_needed'))
                        <a href="{{ route('claim.referral-reward') }}" class="block pt-2">
                            <button class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider transition">
                                Claim Referral Reward!
                            </button>
                        </a>
                    @endif
                </div>

            </div>

            <!-- PHOTOS FEED SECTION -->
            @if(isset($photos) && count($photos) > 0)
                <div class="bg-zinc-900/90 backdrop-blur-md border border-amber-500/30 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-xl">
                    <div class="flex justify-between items-center border-b border-amber-500/20 pb-3 mb-4">
                        <h2 class="text-sm font-extrabold text-amber-400 uppercase tracking-wide">Hotel Moments</h2>
                        <a href="{{ url('/community/photos') }}" class="text-xs font-bold text-amber-400 hover:underline">View all photos &rarr;</a>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 sm:gap-3">
                        @foreach($photos->take(5) as $photo)
                            <div class="h-28 rounded-2xl overflow-hidden border border-amber-500/20 bg-black/50 shadow-inner group relative">
                                <img src="{{ $photo->url }}" alt="Camera Photo" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </main>
    </div>

    <!-- Swiper JS & Copy Script -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        const swiper = new Swiper('.articles-slider', {
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
        });

        function copyCode() {
            let copyText = document.querySelector("#referral");
            copyText.select();
            document.execCommand("copy");

            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Your referral code has been copied to your clipboard.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
    </script>


<!-- Client device guard -->
<div id="client-device-warning" class="fixed inset-0 z-[99999] hidden items-center justify-center bg-black/80 p-4" role="dialog" aria-modal="true" aria-labelledby="client-device-warning-title">
    <div class="w-full max-w-md rounded-3xl border-2 border-amber-500/50 bg-zinc-950 p-5 sm:p-6 shadow-2xl">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-amber-500">Wrong client selected</p>
                <h3 id="client-device-warning-title" class="mt-2 text-xl font-black text-white">Please use the correct client</h3>
            </div>
            <button type="button" id="client-device-warning-close" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-amber-500/40 bg-amber-500/10 text-2xl font-bold text-amber-400 hover:bg-amber-500/20" aria-label="Close">&times;</button>
        </div>
        <p id="client-device-warning-message" class="mt-4 text-sm font-semibold leading-6 text-zinc-300"></p>
        <button type="button" id="client-device-warning-ok" class="mt-5 w-full rounded-2xl border border-amber-400/50 bg-gradient-to-r from-amber-600 to-orange-600 px-4 py-3 text-sm font-black uppercase tracking-wide text-white shadow-lg hover:from-amber-500 hover:to-orange-500">
            OK
        </button>
    </div>
</div>

<script>
(() => {
    const isMobileDevice = () => {
        const ua = navigator.userAgent || navigator.vendor || window.opera || '';
        const mobileUa = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile/i.test(ua);
        const ipadOs = navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1;
        return mobileUa || ipadOs;
    };

    const modal = document.getElementById('client-device-warning');
    const message = document.getElementById('client-device-warning-message');
    const closeButton = document.getElementById('client-device-warning-close');
    const okButton = document.getElementById('client-device-warning-ok');

    const hideWarning = () => {
        modal?.classList.add('hidden');
        modal?.classList.remove('flex');
    };

    const showWarning = (text) => {
        if (!modal || !message) {
            window.alert(text);
            return;
        }

        message.textContent = text;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        okButton?.focus();
    };

    document.addEventListener('click', (event) => {
        const link = event.target.closest('.js-client-device-guard');
        if (!link) return;

        const requiredDevice = link.dataset.clientDevice;
        const mobile = isMobileDevice();

        if (requiredDevice === 'mobile' && !mobile) {
            event.preventDefault();
            event.stopPropagation();
            showWarning('This client is designed for phones and tablets. Please use Play On Computer instead.');
            return;
        }

        if (requiredDevice === 'desktop' && mobile) {
            event.preventDefault();
            event.stopPropagation();
            showWarning('This client is designed for desktop and laptop computers. Please use Play On Phone instead.');
        }
    }, true);

    closeButton?.addEventListener('click', hideWarning);
    okButton?.addEventListener('click', hideWarning);
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) hideWarning();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') hideWarning();
    });
})();
</script>

</body>
</html>