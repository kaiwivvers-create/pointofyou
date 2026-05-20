@extends('layouts.table')

@section('title', 'Scan QR')

@section('content')
    <div class="text-center py-16">
        <p class="text-5xl mb-6">📱</p>
        <h1 class="font-display text-2xl font-semibold text-amber-950 mb-3">Scan your table QR</h1>
        <p class="text-stone-600 leading-relaxed max-w-sm mx-auto">
            Find the QR code on your table to open the menu. No login needed — we'll know which table you're at.
        </p>
        @if (session('error'))
            <p class="mt-6 text-sm text-red-700 bg-red-50 rounded-xl px-4 py-3">{{ session('error') }}</p>
        @endif
    </div>
@endsection
