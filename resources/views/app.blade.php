<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title inertia>{{ config('app.name', 'f33') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Syne:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#E30613">
    <link rel="apple-touch-icon" href="/pwa-icons/icon-192x192.png">

    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta property="og:title" content="{{ config('app.name') }}">
    <meta property="og:description" content="Plataforma profesional para fotógrafos">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ asset('images/logo.png') }}">

    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('ServiceWorker registrado con éxito con el scope: ', registration.scope);
                    })
                    .catch(err => {
                        console.log('Error al registrar ServiceWorker: ', err);
                    });
            });
        }
    </script>
</body>

</html>