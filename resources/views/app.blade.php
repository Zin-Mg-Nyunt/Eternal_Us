<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <link rel="icon" href="/favicon.png" type="image/png">
        <link rel="icon" href="/favicon.png" sizes="any">
        <link rel="apple-touch-icon" href="/favicon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @routes
        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <div id="app-loading" aria-live="polite" aria-label="Loading">
            <div id="app-loading-logo-wrap" class="animate-ping [animation-duration: 2.5s] flex flex-col items-center justify-center gap-3">
                <div id="app-loading-logo"></div>
                <img src="/favicon.png" alt="Loading" class="w-16 h-16">
            </div>
        </div>
        <script>
            (function() {
                var fallbackMs = 3000;

                function forceHideLoading() {
                    var loadingOverlay = document.getElementById('app-loading');
                    if (!loadingOverlay) return;

                    loadingOverlay.style.display = 'none';
                    loadingOverlay.style.opacity = '0';
                    loadingOverlay.style.visibility = 'hidden';

                    if (loadingOverlay.parentNode) {
                        loadingOverlay.parentNode.removeChild(loadingOverlay);
                    }
                }

                window.setTimeout(forceHideLoading, fallbackMs);
            })();
        </script>
        <x-inertia::app />
        <style>
            #app-loading {
                position: fixed;
                inset: 0;
                z-index: 99999;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 0.9rem;
                background: linear-gradient(135deg, #fff1f2, #ffe4e6, #ffe4e6);
                color: #9f1239;
                transition: opacity 0.35s ease, transform 0.38s ease, filter 0.38s ease;
                transform: scale(1);
                filter: blur(0);
            }

            #app-loading p {
                margin: 0;
                font-size: 0.9rem;
                letter-spacing: 0.08em;
                font-weight: 700;
            }

            #app-loading.is-hidden {
                opacity: 0;
                transform: scale(1.035);
                filter: blur(1px);
                pointer-events: none;
            }

            #app-loading.is-hidden #app-loading-logo-wrap {
                animation: app-loading-logo-out 0.36s ease forwards;
            }

            @keyframes app-loading-logo-out {
                from {
                    transform: scale(1);
                    opacity: 1;
                }
                to {
                    transform: scale(1.18);
                    opacity: 0;
                }
            }
        </style>
    </body>
</html>
