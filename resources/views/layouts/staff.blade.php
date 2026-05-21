<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Staff') — Golden Crumb</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50 min-h-screen">
    <div class="flex min-h-screen">
        {{-- Desktop sidebar --}}
        <aside class="hidden lg:flex w-72 shrink-0 flex-col border-r border-slate-700 text-white" style="background-color: #1e293b;">
            <div class="p-6 pb-4">
                <a href="{{ url('/') }}" class="flex items-center gap-3 rounded-lg p-2 -m-2 transition-colors hover:bg-slate-800">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-slate-800 text-xl font-semibold text-amber-500">GC</div>
                    <div>
                        <span class="font-sans text-lg font-semibold text-white leading-tight block">Golden Crumb</span>
                        <span class="text-xs text-slate-400">Staff portal</span>
                    </div>
                </a>
            </div>

            <div class="mx-6 mb-5 rounded-lg bg-slate-800 px-4 py-3 border border-slate-700">
                <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-slate-400 mt-0.5">{{ Auth::user()->role->label() }}</p>
            </div>

            <div class="flex-1 px-4 pb-4 min-h-0">
                @include('partials.staff-sidebar')
            </div>

            <div class="border-t border-slate-700 p-4 mx-2 mb-2">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-slate-300 transition-colors hover:bg-slate-800 hover:text-white">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Sign out
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            {{-- Mobile header + nav --}}
            <header class="lg:hidden border-b border-slate-200 bg-white sticky top-0 z-30">
                <div class="px-4 py-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-sans font-semibold text-slate-900 truncate">Golden Crumb</p>
                        <p class="text-xs text-slate-500 truncate">{{ Auth::user()->name }} · {{ Auth::user()->role->label() }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="shrink-0 text-sm font-semibold text-slate-700 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200">Sign out</button>
                    </form>
                </div>
                <div class="px-4 pb-3 overflow-x-auto">
                    @include('partials.staff-sidebar-mobile')
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-8 lg:p-10 max-w-7xl w-full">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
