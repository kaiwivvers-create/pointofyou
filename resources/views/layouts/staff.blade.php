<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Staff') — Golden Crumb</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fredoka:400,500,600,700|nunito:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-stone-800 bg-[#faf6f0] min-h-screen">
    <div class="flex min-h-screen">
        {{-- Desktop sidebar --}}
        <aside class="hidden lg:flex w-72 shrink-0 flex-col border-r border-amber-900/20 bg-gradient-to-b from-amber-950 via-amber-950 to-[#3d2208] text-amber-100">
            <div class="p-6 pb-4">
                <a href="{{ url('/') }}" class="flex items-center gap-3 rounded-xl p-2 -m-2 transition-colors hover:bg-amber-900/40">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-amber-800/60 text-xl shadow-inner">🥐</span>
                    <div>
                        <span class="font-display text-lg font-medium text-amber-50 leading-tight block">Golden Crumb</span>
                        <span class="text-[11px] text-amber-400/80">Staff portal</span>
                    </div>
                </a>
            </div>

            <div class="mx-6 mb-5 rounded-xl bg-amber-900/35 px-4 py-3 ring-1 ring-amber-800/50">
                <p class="text-sm font-semibold text-amber-50 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-amber-400/90 mt-0.5">{{ Auth::user()->role->label() }}</p>
            </div>

            <div class="flex-1 px-4 pb-4 min-h-0">
                @include('partials.staff-sidebar')
            </div>

            <div class="border-t border-amber-800/60 p-4 mx-2 mb-2">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-amber-300/90 transition-colors hover:bg-amber-900/50 hover:text-amber-50">
                        <span class="flex size-8 items-center justify-center rounded-lg bg-amber-900/40 text-base" aria-hidden="true">🚪</span>
                        Sign out
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            {{-- Mobile header + nav --}}
            <header class="lg:hidden border-b border-amber-200/70 bg-white/80 backdrop-blur-md sticky top-0 z-30">
                <div class="px-4 py-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-display font-medium text-amber-950 truncate">🥐 Golden Crumb</p>
                        <p class="text-xs text-stone-500 truncate">{{ Auth::user()->name }} · {{ Auth::user()->role->label() }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="shrink-0 text-sm font-semibold text-amber-800 px-3 py-1.5 rounded-full bg-amber-100">Sign out</button>
                    </form>
                </div>
                <div class="px-4 pb-3 overflow-x-auto">
                    @include('partials.staff-sidebar-mobile')
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-8 lg:p-10 max-w-6xl w-full">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
