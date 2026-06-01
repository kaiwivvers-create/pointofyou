@php
    $settings = \App\Models\BrandSettings::getSettings();
@endphp
@extends('layouts.bakery')

@section('title', 'Staff Login — ' . ($settings->app_name ?? 'Golden Crumb'))

@section('body-class', 'flex flex-col')

@section('content')
    <style>
        .focus-primary:focus {
            border-color: {{ $settings->primary_color }} !important;
            --tw-ring-color: {{ $settings->primary_color }}33 !important;
        }
    </style>

    <div class="flex-1 flex items-center justify-center px-4 py-12 sm:py-16">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 hover:opacity-80 transition-opacity">
                    @if ($settings->logo)
                        <img src="{{ asset('storage/' . $settings->logo) }}" alt="{{ $settings->app_name }}" class="w-10 h-10 object-cover rounded-lg">
                    @else
                        <div class="flex w-10 h-10 items-center justify-center rounded-lg text-lg font-semibold" style="background-color: {{ $settings->primary_color }}30; color: {{ $settings->primary_font_color }};">
                            {{ $settings->logo_fallback }}
                        </div>
                    @endif
                    <span class="font-display text-2xl font-medium" style="color: {{ $settings->primary_font_color }};">{{ $settings->app_name }}</span>
                </a>
                <p class="mt-3 text-sm text-stone-500">Staff portal</p>
            </div>

            <div class="rounded-3xl bg-white p-8 sm:p-10 shadow-lg shadow-amber-900/5 ring-1 ring-amber-100">
                <h1 class="font-display text-2xl font-semibold text-stone-900 text-center mb-2">Welcome back</h1>
                <p class="text-stone-500 text-center text-sm mb-8">Sign in to manage the bakery</p>

                <x-flash />

                <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-stone-700 mb-1.5">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            class="w-full rounded-xl border px-4 py-3 text-stone-800 placeholder:text-stone-400 focus:ring-2 outline-none transition-shadow focus-primary"
                            style="border-color: {{ $settings->primary_color }}40; background-color: {{ $settings->secondary_color }}40;"
                            placeholder="you@example.com"
                        >
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-stone-700 mb-1.5">Password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-xl border px-4 py-3 text-stone-800 placeholder:text-stone-400 focus:ring-2 outline-none transition-shadow focus-primary"
                            style="border-color: {{ $settings->primary_color }}40; background-color: {{ $settings->secondary_color }}40;"
                            placeholder="••••••••"
                        >
                    </div>

                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            class="size-4 rounded text-stone-800 focus:ring-amber-300"
                            @checked(old('remember'))
                        >
                        <span class="text-sm text-stone-600">Remember me</span>
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-full py-3.5 text-sm font-semibold transition-colors"
                        style="background-color: {{ $settings->primary_color }}; color: {{ $settings->primary_font_color }}; filter: brightness(1);"
                        onmouseenter="this.style.filter='brightness(0.95)'"
                        onmouseleave="this.style.filter='brightness(1)'"
                    >
                        Sign in
                    </button>
                </form>
            </div>

            <p class="mt-8 text-center">
                <a href="{{ url('/') }}" class="text-sm text-stone-500 hover:opacity-80 transition-colors" style="color: {{ $settings->primary_color }};">
                    ← Back to bakery
                </a>
            </p>
        </div>
    </div>
@endsection
