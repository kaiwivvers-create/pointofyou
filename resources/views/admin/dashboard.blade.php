@extends('layouts.staff')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Admin dashboard</h1>
            <p class="staff-page-subtitle">Manage food, drinks, and table QR codes.</p>
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

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('admin.menu.create') }}" class="staff-btn-primary">Add menu item</a>
        <a href="{{ route('admin.menu.index') }}" class="staff-btn-secondary">Edit menu</a>
        <a href="{{ route('admin.tables.index') }}" class="staff-btn-secondary">Table QR codes</a>
    </div>
@endsection
