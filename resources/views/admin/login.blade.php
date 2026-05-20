@extends('layouts.bakery')

@section('title', 'Staff Login — Golden Crumb')

@section('body-class', 'flex flex-col')

@section('content')
    <div class="flex-1 flex items-center justify-center px-4 py-12 sm:py-16">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-amber-950 hover:text-amber-800 transition-colors">
                    <span class="text-2xl" aria-hidden="true">🥐</span>
                    <span class="font-display text-2xl font-medium">Golden Crumb</span>
                </a>
                <p class="mt-3 text-sm text-stone-500">Staff portal</p>
            </div>

            <div class="rounded-3xl bg-white p-8 sm:p-10 shadow-lg shadow-amber-900/5 ring-1 ring-amber-100">
                <h1 class="font-display text-2xl font-semibold text-amber-950 text-center mb-2">Welcome back</h1>
                <p class="text-stone-500 text-center text-sm mb-8">Sign in to manage the bakery</p>

                @if ($errors->any())
                    <div class="mb-6 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-100" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

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
                            class="w-full rounded-xl border border-amber-200/80 bg-[#faf6f0] px-4 py-3 text-stone-800 placeholder:text-stone-400 focus:border-amber-400 focus:ring-2 focus:ring-amber-200 outline-none transition-shadow"
                            placeholder="you@goldencrumb.com"
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
                            class="w-full rounded-xl border border-amber-200/80 bg-[#faf6f0] px-4 py-3 text-stone-800 placeholder:text-stone-400 focus:border-amber-400 focus:ring-2 focus:ring-amber-200 outline-none transition-shadow"
                            placeholder="••••••••"
                        >
                    </div>

                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            class="size-4 rounded border-amber-300 text-amber-800 focus:ring-amber-300"
                            @checked(old('remember'))
                        >
                        <span class="text-sm text-stone-600">Remember me</span>
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-full bg-amber-800 py-3.5 text-sm font-semibold text-amber-50 hover:bg-amber-900 transition-colors"
                    >
                        Sign in
                    </button>
                </form>
            </div>

            <p class="mt-8 text-center">
                <a href="{{ url('/') }}" class="text-sm text-stone-500 hover:text-amber-800 transition-colors">
                    ← Back to bakery
                </a>
            </p>
        </div>
    </div>
@endsection
