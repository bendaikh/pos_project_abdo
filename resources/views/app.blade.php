<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <title>{{ config('app.name', 'GreenPOS') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|sora:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        // Force refresh stale PWA caches after multi-PDV deploy
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then((regs) => {
                regs.forEach((reg) => reg.update());
            });
            if (caches && caches.keys) {
                caches.keys().then((keys) => {
                    keys.filter((k) => k.startsWith('greenpos-') && k !== 'greenpos-v7-unites-mesure')
                        .forEach((k) => caches.delete(k));
                });
            }
        }
    </script>
</head>
<body class="antialiased bg-bg-main text-white">
    <div id="app"></div>
</body>
</html>
