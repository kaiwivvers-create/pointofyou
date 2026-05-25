@extends('layouts.staff')

@section('title', 'Tables & QR')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Tables & QR codes</h1>
            <p class="staff-page-subtitle">Customers scan these — no login. Print each URL as a QR code.</p>
        </div>
    </div>

    <x-flash />

    @if (auth()->user()->isSuperAdmin())
        <form method="POST" action="{{ route('super-admin.tables.store') }}" class="staff-card p-5 mb-8 flex flex-col sm:flex-row gap-3 max-w-lg">
            @csrf
            <input type="text" name="name" required placeholder="Table name (e.g. Patio 4)" class="staff-input flex-1">
            <button type="submit" class="staff-btn-primary shrink-0">Add table</button>
        </form>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @foreach ($tables as $table)
            <div class="staff-card p-6">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <h2 class="font-sans text-xl font-semibold text-slate-900">{{ $table->name }}</h2>
                        <p class="text-xs text-slate-500 mt-1 font-mono">token: {{ $table->token }}</p>
                    </div>
                    @if (auth()->user()->isSuperAdmin())
                        <form method="POST" action="{{ route('super-admin.tables.destroy', $table) }}" onsubmit="return confirm('Delete this table?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="staff-link-danger text-xs">Delete</button>
                        </form>
                    @endif
                </div>
                <p class="text-sm font-semibold text-slate-600 mb-2">QR Code</p>
                <div class="bg-white rounded-lg p-4 flex justify-center mb-4 ring-1 ring-slate-200">
                    {!! $table->qrCode() !!}
                </div>
                @if (auth()->user()->isSuperAdmin())
                    <form method="POST" action="{{ route('super-admin.tables.regenerate-qr', $table) }}" class="mb-4">
                        @csrf
                        <button type="submit" class="w-full text-xs bg-slate-100 text-slate-700 py-2 rounded-lg hover:bg-slate-200 transition-colors">Regenerate QR Code</button>
                    </form>
                @endif
                <p class="text-sm font-semibold text-slate-600 mb-2">QR scan URL</p>
                <code class="block text-xs bg-slate-50 rounded-lg p-4 break-all text-slate-900 ring-1 ring-slate-200 mb-4 leading-relaxed">{{ $table->scanUrl() }}</code>
                <a href="{{ $table->scanUrl() }}" target="_blank" class="staff-link text-sm">Preview customer menu →</a>
            </div>
        @endforeach
    </div>
@endsection
