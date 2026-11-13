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

        @php
            $metaTitle = $meta['title'] ?? null ?: config('app.name', 'Laravel');
            $metaDescription = $meta['description'] ?? null;
            $metaImage = $meta['image'] ?? null;
            $preloadImage = $meta['preload'] ?? null;
        @endphp
        <title inertia>{{ $metaTitle }}</title>
        <link rel="canonical" href="{{ url()->current() }}">
        @if ($metaDescription)
            <meta inertia="description" name="description" content="{{ $metaDescription }}">
        @endif
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ config('app.name') }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="{{ $metaTitle }}">
        @if ($metaDescription)
            <meta property="og:description" content="{{ $metaDescription }}">
        @endif
        @if ($metaImage && ! preg_match('/\.(mp4|webm|mov|avi|m3u8)$/i', parse_url($metaImage, PHP_URL_PATH) ?? ''))
            <meta property="og:image" content="{{ $metaImage }}">
        @endif
        <meta name="twitter:card" content="{{ $metaImage ? 'summary_large_image' : 'summary' }}">

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @if ($preloadImage)
            <link rel="preload" as="image" href="{{ $preloadImage['href'] }}" imagesrcset="{{ $preloadImage['imagesrcset'] }}" imagesizes="{{ $meta['preloadSizes'] ?? '100vw' }}" type="{{ $preloadImage['type'] }}" fetchpriority="high">
        @endif

        {{-- Avenir-gewichten die boven de vouw op vrijwel elke pagina staan --}}
        <link rel="preload" as="font" type="font/woff2" href="{{ Vite::asset('resources/fonts/Avenir-BlackOblique-04.woff2') }}" crossorigin>
        <link rel="preload" as="font" type="font/woff2" href="{{ Vite::asset('resources/fonts/Avenir-Roman-12.woff2') }}" crossorigin>

        {{-- Instrument Sans is alleen een fallback voor tekst zonder font-avenir; niet render-blocking laden --}}
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link rel="stylesheet" href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" media="print" onload="this.media='all'">
        <noscript><link rel="stylesheet" href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap"></noscript>

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
