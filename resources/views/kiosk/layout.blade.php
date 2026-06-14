<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $brandSettings = \App\Models\BrandSettings::getSettings();
        $favicon = $brandSettings->logo ? asset('app-storage/' . $brandSettings->logo) : asset('favicon.ico');
        $appName = $brandSettings->app_name ?? config('app.name');
    @endphp
    <title>Kiosk - {{ $appName }}</title>
    <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fredoka:400,500,600,700|nunito:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary-color: {{ $brandSettings->primary_color }};
            --primary-font-color: {{ $brandSettings->primary_font_color }};
            --secondary-color: {{ $brandSettings->secondary_color }};
            --accent-color: {{ $brandSettings->accent_color }};
        }
        .text-primary-font {
            color: var(--primary-font-color) !important;
        }
        html, body {
            font-family: 'Nunito', sans-serif;
            background-color: #faf6f0;
            color: #292524;
            -webkit-tap-highlight-color: transparent;
            margin: 0;
            padding: 0;
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
        .dragging {
            cursor: grabbing !important;
            user-select: none;
        }
    </style>
</head>
<body class="h-screen bg-[#faf6f0] selection:bg-amber-500 selection:text-white flex flex-col overflow-auto">
    @yield('content')
    @include('partials.translator', ['isFloating' => true])
</body>
</html>
