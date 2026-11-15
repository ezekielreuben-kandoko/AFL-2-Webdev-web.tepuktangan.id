<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#263317" />
    <title>@yield('title')</title>
    <link
        rel="preload"
        href="{{ asset('font/GlacialIndifference-Regular.otf') }}"
        as="font"
        type="font/otf"
        crossorigin
    />
    <link rel="preload" href="{{ asset('font/GlacialIndifference-Bold.otf') }}" as="font" type="font/otf" crossorigin />
    <link
        rel="preload"
        href="{{ asset('font/BodoniModa-VariableFont_opszwght-BF6437720bbd36c.ttf') }}"
        as="font"
        type="font/ttf"
        crossorigin
    />
    <link rel="preconnect" href="https://lh3.googleusercontent.com" />
    <link rel="preconnect" href="https://drive.google.com" />
    <link rel="stylesheet" href="{{ asset('mydesign/style.css') }}?v={{ filemtime(public_path('mydesign/style.css')) }}" />
    @stack('head')
</head>
<body>
    @include('components.navbar')

    <main>
        @yield('content')
    </main>

    @include('layouts.footer')
    <script src="{{ asset('mydesign/app.js') }}?v={{ filemtime(public_path('mydesign/app.js')) }}" defer></script>
</body>
</html>
