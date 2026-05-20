<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Menu') — Golden Crumb</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fredoka:400,500,600,700|nunito:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-stone-800 bg-[#faf6f0] min-h-screen pb-28">
    <header class="sticky top-0 z-40 bg-[#faf6f0]/95 backdrop-blur border-b border-amber-200/60 px-4 py-4">
        <div class="flex items-center justify-between gap-3 max-w-lg mx-auto">
            <div>
                <p class="font-display text-lg font-medium text-amber-950">🥐 Golden Crumb</p>
                @if (session('cafe_table_name'))
                    <p class="text-sm text-amber-800/80">You're at <span class="font-semibold">{{ session('cafe_table_name') }}</span></p>
                @endif
            </div>
            @hasSection('header-action')
                @yield('header-action')
            @endif
        </div>
    </header>
    <main class="px-4 py-6 max-w-lg mx-auto">
        @yield('content')
    </main>
</body>
</html>
