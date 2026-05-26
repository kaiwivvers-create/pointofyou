<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Menu') — Golden Crumb</title>
    @php
        $brandSettings = \App\Models\BrandSettings::getSettings();
        $favicon = $brandSettings->logo ? asset('storage/' . $brandSettings->logo) : asset('favicon.ico');
    @endphp
    <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fredoka:400,500,600,700|nunito:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #faf6f0;
            color: #292524;
            -webkit-tap-highlight-color: transparent;
        }

        h1, h2, h3, h4, h5, h6, .font-display {
            font-family: 'Fredoka', sans-serif;
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .item-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .item-card:active {
            transform: scale(0.97);
        }
    </style>
</head>
<body class="min-h-screen bg-[#faf6f0] selection:bg-amber-500 selection:text-white">
    @yield('content')
</body>
</html>
