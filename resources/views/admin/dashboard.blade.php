@extends('layouts.staff')

@section('title', 'Dashboard')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Hello, {{ Auth::user()->name }}</h1>
            <p class="staff-page-subtitle">Welcome to your dashboard. Use the sidebar to navigate.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
        <div class="staff-stat-card">
            <p class="staff-stat-value">{{ $menuCount }}</p>
            <p class="staff-stat-label">Menu items</p>
        </div>
        <div class="staff-stat-card">
            <p class="staff-stat-value">{{ $tableCount }}</p>
            <p class="staff-stat-label">Tables</p>
        </div>
        <div class="staff-stat-card">
            <p class="staff-stat-value">{{ $pendingOrders }}</p>
            <p class="staff-stat-label">Orders awaiting payment</p>
        </div>
    </div>

    @if (Auth::user()->role->value === 'admin')
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('admin.menu.create') }}" class="staff-btn-primary">Add menu item</a>
        <a href="{{ route('admin.menu.index') }}" class="staff-btn-secondary">Edit menu</a>
        <a href="{{ route('admin.tables.index') }}" class="staff-btn-secondary">Table QR codes</a>
    </div>
    @endif
@endsection
