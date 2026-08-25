<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lounge - Create Your Character</title>

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
            background: #0f0507 url('https://loungehotel.org/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png') no-repeat center center fixed;
            background-size: cover;
        }

        .lounge-hero-bg {
            background-image: linear-gradient(to bottom, rgba(15, 5, 7, 0.8), rgba(15, 5, 7, 0.9)),
                              url('https://loungehotel.org/assets/images/1661a9e4-324e-4336-885b-c1c707fd7db7.png');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 lg:p-12 relative overflow-x-hidden antialiased">

    <!-- Dark Overlay -->
    <div class="fixed inset-0 bg-black/60 backdrop-blur-[2px] z-0"></div>

    <!-- Main Container -->
    <main class="relative z-10 w-full max-w-6xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">

            <!-- Left Panel: Avatar Look Customizer -->
            <div class="lg:col-span-5 lounge-hero-bg border-2 border-amber-500/30 rounded-3xl p-6 lg:p-8 text-white shadow-2xl flex flex-col justify-between items-center text-center relative">

                <div class="w-full">
                    <div class="flex justify-center mb-4">
                        <img src="https://loungehotel.org/assets/images/lounge.png" alt="Lounge Hotel Logo" class="h-12 w-auto drop-shadow-lg">
                    </div>
                    <h2 class="text-2xl font-extrabold text-amber-400">Design Your Avatar</h2>
                    <p class="text-xs text-amber-100/70 mt-1">Select a style or preset for your hotel character.</p>
                </div>

                <!-- Avatar Preview -->
                <div class="my-6 relative bg-black/60 border border-amber-500/40 rounded-2xl p-6 w-full flex flex-col items-center justify-center shadow-inner min-h-[220px]">
                    <img id="avatar-preview"
                         src="https://www.habbo.com/habbo-imaging/avatarimage?figure=hr-115-42.hd-190-1.ch-215-62.lg-270-62.sh-290-62&direction=2&head_direction=3&gesture=sml&size=l"
                         alt="Avatar Preview"
                         class="drop-shadow-2xl transition-all duration-300">

                    <!-- Gender Selector Buttons -->
                    <div class="flex gap-3 mt-4">
                        <button type="button" onclick="setGender('M')" id="btn-gender-m" class="px-4 py-1.5 rounded-lg text-xs font-bold uppercase bg-amber-500 text-black border border-amber-400 shadow transition-all">
                            Male
                        </button>
                        <button type="button" onclick="setGender('F')" id="btn-gender-f" class="px-4 py-1.5 rounded-lg text-xs font-bold uppercase bg-black/60 text-zinc-400 border border-amber-500/30 hover:text-white transition-all">
                            Female
                        </button>
                    </div>
                </div>

                <!-- Style Presets Grid -->
                <div class="w-full">
                    <label class="block text-xs font-bold uppercase text-amber-200/80 mb-2">Select Style Preset</label>
                    <div class="grid grid-cols-5 gap-2" id="preset-container">
                        <!-- Presets dynamically generated via JS -->
                    </div>
                </div>

            </div>

            <!-- Right Panel: Account Details Form -->
            <div class="lg:col-span-7 bg-zinc-900/95 backdrop-blur-md border-2 border-amber-500/40 rounded-3xl p-6 lg:p-8 shadow-2xl relative flex flex-col justify-between">

                <div>
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-3xl font-extrabold text-amber-400">Join LoungeHotel</h2>
                            <p class="text-sm text-zinc-400 mt-1">Create your free account and start exploring.</p>
                        </div>
                        <a href="{{ url('/') }}" class="text-xs font-bold text-amber-400/80 hover:text-amber-400 uppercase tracking-wider">
                            &larr; Back to Login
                        </a>
                    </div>

                    <!-- Display Global Validation Errors if any field fails -->
                    @if ($errors->any())
                        <div class="mb-4 p-4 rounded-xl bg-red-950/80 border border-red-500/50 text-red-200 text-xs space-y-1">
                            <p class="font-bold uppercase tracking-wide">Please fix the following issues:</p>
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" id="register-form" class="space-y-4">
                        @csrf

                        <!-- Hidden Inputs required by Atom CMS -->
                        <input type="hidden" name="look" id="figure-input-look" value="hr-115-42.hd-190-1.ch-215-62.lg-270-62.sh-290-62">
                        <input type="hidden" name="figure" id="figure-input-fig" value="hr-115-42.hd-190-1.ch-215-62.lg-270-62.sh-290-62">
                        <input type="hidden" name="gender" id="gender-input" value="M">
                        <input type="hidden" name="referral_code" value="{{ $referral_code ?? '' }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="username" class="block text-xs font-bold uppercase text-amber-200/80 mb-1">Username</label>
                                <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus
                                       class="w-full px-4 py-3 rounded-xl bg-black/50 border border-amber-500/30 text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>

                            <div>
                                <label for="mail" class="block text-xs font-bold uppercase text-amber-200/80 mb-1">Email Address</label>
                                <input id="mail" type="email" name="mail" value="{{ old('mail') }}" required
                                       class="w-full px-4 py-3 rounded-xl bg-black/50 border border-amber-500/30 text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-xs font-bold uppercase text-amber-200/80 mb-1">Password</label>
                                <input id="password" type="password" name="password" required
                                       class="w-full px-4 py-3 rounded-xl bg-black/50 border border-amber-500/30 text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-xs font-bold uppercase text-amber-200/80 mb-1">Repeat Password</label>
                                <input id="password_confirmation" type="password" name="password_confirmation" required
                                       class="w-full px-4 py-3 rounded-xl bg-black/50 border border-amber-500/30 text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                        </div>

                        @if (setting('requires_beta_code'))
                            <div>
                                <label for="beta_code" class="block text-xs font-bold uppercase text-amber-200/80 mb-1">Beta Code</label>
                                <input id="beta_code" type="text" name="beta_code" value="{{ old('beta_code') }}"
                                       class="w-full px-4 py-3 rounded-xl bg-black/50 border border-amber-500/30 text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                        @endif

                        <div class="flex items-center gap-x-3 pt-2">
                            <input id="terms" type="checkbox" name="terms" required class="rounded border-amber-500 text-red-700 focus:ring-amber-500">
                            <label for="terms" class="text-xs font-semibold text-zinc-300">
                                I accept the hotel terms & rules.
                            </label>
                        </div>

                        @if (setting('google_recaptcha_enabled'))
                            <div class="mt-4 g-recaptcha" data-sitekey="{{ config('habbo.site.recaptcha_site_key') }}"></div>
                        @endif

                        @if (setting('cloudflare_turnstile_enabled'))
                            <x-turnstile />
                        @endif

                        <div class="pt-2">
                            <button type="submit" class="w-full py-4 px-6 rounded-xl font-bold text-amber-100 bg-gradient-to-r from-red-900 via-red-800 to-amber-700 hover:from-red-800 hover:to-amber-600 border border-amber-400/40 shadow-xl transform active:scale-95 transition-all text-sm uppercase tracking-wide">
                                Create My Account &rarr;
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-6 text-center text-xs text-zinc-400 border-t border-amber-500/20 pt-4">
                    Already have an account?
                    <a href="{{ url('/') }}" class="font-bold text-amber-400 hover:underline">
                        Sign in here &rarr;
                    </a>
                </div>

            </div>

        </div>
    </main>

    <!-- Character Customizer Script -->
    <script>
        const maleLooks = [
            'hr-115-42.hd-190-1.ch-215-62.lg-270-62.sh-290-62',
            'hr-828-1036.hd-180-1.ch-255-66.lg-280-110.sh-305-62',
            'hr-125-31.hd-209-1.ch-210-1321.lg-275-62.sh-295-62',
            'hr-3185-1362.hd-180-10.ch-3030-82.lg-275-110.sh-305-62',
            'hr-831-31.hd-180-3.ch-215-110.lg-280-110.sh-300-62',
            'hr-165-31.hd-190-2.ch-220-66.lg-270-82.sh-290-62.ca-1808-62',
            'hr-170-42.hd-185-1.ch-235-82.lg-285-82.sh-305-110.he-1603-62',
            'hr-834-1036.hd-200-1.ch-210-82.lg-275-82.sh-295-62.ey-1302-62',
            'hr-110-31.hd-180-1.ch-250-62.lg-280-62.sh-300-62',
            'hr-155-37.hd-195-2.ch-215-82.lg-270-110.sh-305-62'
        ];

        const femaleLooks = [
            'hr-515-33.hd-600-1.ch-635-73.lg-700-82.sh-725-62',
            'hr-807-1035.hd-600-1.ch-665-110.lg-710-110.sh-735-62',
            'hr-831-45.hd-605-1.ch-685-82.lg-715-82.sh-720-62',
            'hr-500-45.hd-600-1.ch-630-1321.lg-695-62.sh-730-62',
            'hr-540-33.hd-600-2.ch-660-82.lg-700-110.sh-725-62.he-1610-62',
            'hr-810-1036.hd-605-1.ch-640-110.lg-710-82.sh-730-62',
            'hr-510-38.hd-600-3.ch-635-82.lg-705-82.sh-720-62.ey-1302-62',
            'hr-827-33.hd-600-1.ch-670-73.lg-715-110.sh-725-62',
            'hr-505-42.hd-605-2.ch-645-110.lg-700-82.sh-735-62',
            'hr-520-45.hd-600-1.ch-650-82.lg-710-82.sh-730-62'
        ];

        let currentGender = 'M';

        function updateAvatarPreview(look) {
            document.getElementById('figure-input-look').value = look;
            document.getElementById('figure-input-fig').value = look;
            document.getElementById('avatar-preview').src = `https://www.habbo.com/habbo-imaging/avatarimage?figure=${look}&direction=2&head_direction=3&gesture=sml&size=l`;
        }

        function setGender(gender) {
            currentGender = gender;
            document.getElementById('gender-input').value = gender;

            const btnM = document.getElementById('btn-gender-m');
            const btnF = document.getElementById('btn-gender-f');

            if (gender === 'M') {
                btnM.className = 'px-4 py-1.5 rounded-lg text-xs font-bold uppercase bg-amber-500 text-black border border-amber-400 shadow transition-all';
                btnF.className = 'px-4 py-1.5 rounded-lg text-xs font-bold uppercase bg-black/60 text-zinc-400 border border-amber-500/30 hover:text-white transition-all';
                renderPresets(maleLooks);
                updateAvatarPreview(maleLooks[0]);
            } else {
                btnF.className = 'px-4 py-1.5 rounded-lg text-xs font-bold uppercase bg-amber-500 text-black border border-amber-400 shadow transition-all';
                btnM.className = 'px-4 py-1.5 rounded-lg text-xs font-bold uppercase bg-black/60 text-zinc-400 border border-amber-500/30 hover:text-white transition-all';
                renderPresets(femaleLooks);
                updateAvatarPreview(femaleLooks[0]);
            }
        }

        function renderPresets(looks) {
            const container = document.getElementById('preset-container');
            container.innerHTML = '';

            looks.forEach((look, index) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'bg-black/60 hover:bg-black/90 border border-amber-500/30 hover:border-amber-400 rounded-xl p-1.5 flex items-center justify-center transition-all transform active:scale-95';
                btn.onclick = () => updateAvatarPreview(look);

                const img = document.createElement('img');
                img.src = `https://www.habbo.com/habbo-imaging/avatarimage?figure=${look}&headonly=1&direction=2&head_direction=2&size=m`;
                img.alt = `Preset ${index + 1}`;

                btn.appendChild(img);
                container.appendChild(btn);
            });
        }

        renderPresets(maleLooks);
    </script>
</body>
</html>