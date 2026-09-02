<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ setting('hotel_name') }} - Nitro</title>

    <link href="https://fonts.googleapis.com/css2?family=Ubuntu+Condensed&display=swap" rel="stylesheet">

    @vite(['resources/themes/' .  setting('theme') . '/css/app.css', 'resources/themes/' .  setting('theme') . '/js/app.js'], 'build')

    <style>
        /* Server Switcher Styles */
        .server-switcher {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 9999;
            background: #000;
            padding: 5px 15px 5px 20px;
            border-bottom-right-radius: 10px;
            display: flex;
            gap: 10px;
            border: 1px solid #333;
            border-top: none;
            border-left: none;
        }
        .server-btn {
            background: #333; color: white; border: 1px solid #555;
            padding: 5px 10px; cursor: pointer; font-family: sans-serif; font-size: 12px;
            text-decoration: none; user-select: none;
        }
        .server-btn:hover { background: #555; }
        .server-btn.active { background: #47b018; border-color: #47b018; font-weight: bold; }
        .server-status { color: white; font-size: 12px; align-self: center; margin-right: 5px; }
    </style>
</head>

<body class="overflow-hidden" id="nitro-client">

    {{-- SERVER SWITCHER (UK & USA) --}}
    <div class="server-switcher">
        <span class="server-status">CURRENT: <b id="status-text">...</b></span>

        <button class="server-btn" id="btn-uk" onclick="switchServer('uk')">
            UK
        </button>

        <button class="server-btn" id="btn-usa" onclick="switchServer('usa')">
            USA
        </button>
    </div>

    {{-- CLIENT BUTTONS --}}
    <div class="absolute left-4 z-10 flex gap-x-2" style="top: 50px;">
        <a data-turbolinks="false" href="{{ route('me.show') }}">
            <x-client.client-button>
                <x-icons.home />
            </x-client.client-button>
        </a>

        <div onclick="reloadClient()">
            <x-client.client-button>
                <x-icons.reload />
            </x-client.client-button>
        </div>

        <div onclick="toggleFullscreen()">
            <x-client.client-button>
                <x-icons.fullscreen />
            </x-client.client-button>
        </div>

        <x-client.client-button classes="flex items-center justify-center gap-x-1">
            <x-icons.user />
            <span id="online-count">0</span>
        </x-client.client-button>
    </div>

    {{-- IFRAME --}}
    <iframe id="nitro" src=""
        class="absolute top-0 left-0 m-0 h-full w-full overflow-hidden border-none p-0"></iframe>

    {{-- Show disconnected message --}}
    <div id="disconnected" class="h-screen w-full" style="display:none;">
        <div class="absolute h-full w-full bg-black/50"></div>

        <div class="relative flex h-full w-full flex-col items-center justify-center gap-4">
            <h2 class="text-2xl text-white">
                {{ __('Whoops! It seems like you have been disconnected...') }}
            </h2>

            <div class="flex gap-x-4">
                <button
                    class="py-2 px-4 text-white rounded bg-[#eeb425] hover:bg-[#e3aa1e] border-2 border-[#cf9d15] transition ease-in-out"
                    onclick="reloadClient()">
                    {{ __('Reload client') }}
                </button>

                <a href="{{ route('me.show') }}">
                    <x-form.secondary-button>
                        {{ __('Back to website') }}
                    </x-form.secondary-button>
                </a>
            </div>
        </div>
    </div>

    <script>
        // CONFIGURATION
        const CONFIG = {
            sso: "{{ $sso }}",
            basePath: "{{ setting('nitro_path') }}",
            proxyUk: "wss://poland.proxypanel.co.uk:9764",
            proxyUsa: "wss://us.proxypanel.co.uk:1000"
        };

        // CLIENT MODE
        const pageParams = new URLSearchParams(window.location.search);
        const mobileMode = pageParams.get('mobile') === '1';

        // SWITCH SERVER LOGIC
        function switchServer(region) {
            localStorage.setItem('nitro_region', region);

            let selectedIp = (region === 'usa') ? CONFIG.proxyUsa : CONFIG.proxyUk;
            let statusText = (region === 'usa') ? "USA" : "UK";

            region = (region === 'usa') ? 'usa' : 'uk';

            // Update UI
            document.getElementById('status-text').innerText = statusText;
            document.title = "{{ setting('hotel_name') }} - Nitro [" + statusText + "]";

            document.getElementById('btn-uk').className = (region === 'uk') ? 'server-btn active' : 'server-btn';
            document.getElementById('btn-usa').className = (region === 'usa') ? 'server-btn active' : 'server-btn';

            // Set iframe src
            const iframe = document.getElementById('nitro');

            const clientParams = new URLSearchParams({
                sso: CONFIG.sso,
                ip: selectedIp
            });

            if (mobileMode) {
                clientParams.set('mobile', '1');
            }

            const targetSrc = `${CONFIG.basePath}/index.html?${clientParams.toString()}`;

            if (iframe.src !== targetSrc) {
                iframe.src = targetSrc;
            }

            // Preserve both region and client mode in the address bar
            const pageUrl = new URL(window.location.href);
            pageUrl.searchParams.set('region', region);

            if (mobileMode) {
                pageUrl.searchParams.set('mobile', '1');
            } else {
                pageUrl.searchParams.delete('mobile');
            }

            window.history.replaceState({}, document.title, pageUrl.pathname + pageUrl.search);
        }

        function toggleFullscreen() {
            if (document.fullscreenElement) {
                document.exitFullscreen();
                return;
            }
            document.documentElement.requestFullscreen();
        }

        function reloadClient() {
            window.location.reload();
        }

        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const urlRegion = urlParams.get('region');
            let savedRegion = urlRegion || localStorage.getItem('nitro_region') || 'uk';

            switchServer(savedRegion);

            function getOnlineUserCount() {
                fetch('{{ route('api.online-count') }}')
                    .then(res => res.json())
                    .then(data => {
                        const count = data.onlineCount ?? data.online_count ?? 0;
                        document.getElementById('online-count').innerText = count;
                    })
                    .catch(() => {});
            }

            getOnlineUserCount();
            setInterval(getOnlineUserCount, 15000);
        });
    </script>

    <script src="{{ asset('assets/js/atom.js') }}"></script>
</body>

</html>