@php
    $brandSettings = \App\Models\BrandSettings::getSettings();
    $favicon = $brandSettings->logo ? asset('storage/' . $brandSettings->logo) : asset('favicon.ico');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Staff') — {{ $brandSettings->app_name }}</title>
    <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
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
    </style>
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50 min-h-screen">
    <div class="flex min-h-screen">
        {{-- Desktop sidebar --}}
        <aside class="hidden lg:flex w-72 shrink-0 flex-col fixed top-0 left-0 h-full overflow-hidden" style="background-color: {{ $brandSettings->secondary_color }}; border-right: 1px solid {{ $brandSettings->primary_color }}40;">
            <div class="p-6 pb-4 flex-shrink-0">
                <a href="{{ url('/') }}" class="flex items-center gap-3 rounded-lg p-2 -m-2 transition-colors" style="--hover-bg: {{ $brandSettings->primary_color }}18;" onmouseenter="this.style.backgroundColor=this.style.getPropertyValue('--hover-bg')" onmouseleave="this.style.backgroundColor=''">
                    @if ($brandSettings->logo)
                        <img src="{{ asset('storage/' . $brandSettings->logo) }}" alt="{{ $brandSettings->app_name }}" class="size-10 rounded-lg object-cover">
                    @else
                        <div class="flex size-10 items-center justify-center rounded-lg text-xl font-semibold" style="background-color: {{ $brandSettings->primary_color }}30; color: {{ $brandSettings->primary_font_color }};">
                            {{ $brandSettings->logo_fallback }}
                        </div>
                    @endif
                    <div>
                        <span class="font-sans text-lg font-semibold leading-tight block" style="color: {{ $brandSettings->primary_font_color }};">{{ $brandSettings->app_name }}</span>
                        <span class="text-xs" style="color: {{ $brandSettings->primary_font_color }}99;">Staff portal</span>
                    </div>
                </a>
            </div>

            <div class="mx-6 mb-5 rounded-lg px-4 py-3 shadow-sm flex-shrink-0" style="background-color: {{ $brandSettings->primary_color }}12; border: 1px solid {{ $brandSettings->primary_color }}30;">
                <p class="text-sm font-semibold truncate" style="color: {{ $brandSettings->primary_font_color }};">{{ Auth::user()->name }}</p>
                <p class="text-xs mt-0.5" style="color: {{ $brandSettings->primary_font_color }}99;">{{ Auth::user()->role->label() }}</p>
            </div>

            <div class="flex-1 px-4 pb-4 overflow-y-auto">
                @include('partials.staff-sidebar')
            </div>

            <div class="p-4 mx-2 mb-2 no-print flex-shrink-0" style="border-top: 1px solid {{ $brandSettings->primary_color }}30;">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium transition-colors"
                        style="color: {{ $brandSettings->primary_font_color }};"
                        onmouseenter="this.style.backgroundColor='{{ $brandSettings->primary_color }}18'""
                        onmouseleave="this.style.backgroundColor=''">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Sign out
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 lg:ml-72 w-full">
            {{-- Mobile header + nav --}}
            <header class="lg:hidden border-b border-slate-200 bg-white sticky top-0 z-30">
                <div class="px-4 py-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-sans font-semibold text-slate-900 truncate">{{ $brandSettings->app_name }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ Auth::user()->name }} · {{ Auth::user()->role->label() }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}" class="no-print">
                        @csrf
                        <button type="submit" class="shrink-0 text-sm font-semibold text-slate-700 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200">Sign out</button>
                    </form>
                </div>
                <div class="px-4 pb-3 overflow-x-auto">
                    @include('partials.staff-sidebar-mobile')
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-8 lg:p-10 w-full overflow-auto">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>
