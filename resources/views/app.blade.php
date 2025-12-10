<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Empresa') }}</title>

        <!--  FAVICON Y META TAGS -->
        <!-- Favicon básico (IE, navegadores antiguos) -->
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        
        <!-- Favicon moderno (PNG de alta resolución) -->
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon.png') }}">
        
        <!-- Apple Touch Icon (iOS, Safari) -->
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
        
        <!-- Manifest para PWA (opcional) -->
        <!--link rel="manifest" href="{{ asset('site.webmanifest') }}"-->
        
        <!-- Meta tags para redes sociales (Open Graph) -->
        <meta property="og:image" content="{{ asset('images/logo.png') }}">
        <meta property="og:title" content="{{ config('app.name') }}">
        <meta property="og:description" content="Plataforma profesional para fotógrafos">
        
        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="{{ asset('images/logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
