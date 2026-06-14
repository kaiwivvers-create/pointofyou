@php
    $settings = \App\Models\BrandSettings::getSettings();
    $favicon = $settings->logo ? asset('app-storage/' . $settings->logo) : asset('favicon.ico');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $settings->app_name)</title>
    <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fredoka:400,500,600,700|nunito:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-stone-800 min-h-screen @yield('body-class')" style="background-color: {{ $settings->secondary_color }};">
    @yield('content')
    @include('partials.translator', ['isFloating' => true])
</body>
</html>
