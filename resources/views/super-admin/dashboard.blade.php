@extends('layouts.staff')

@section('title', 'Super Admin')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Super Admin Dashboard</h1>
            <p class="staff-page-subtitle">Full access to staff, menu, tables, and payments.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        <div class="staff-stat-card">
            <p class="staff-stat-value">{{ $staffCount }}</p>
            <p class="staff-stat-label">Staff users</p>
        </div>
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
            <p class="staff-stat-label">Awaiting payment</p>
        </div>
    </div>

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('super-admin.users.create') }}" class="staff-btn-primary">Add staff user</a>
        <a href="{{ route('admin.menu.index') }}" class="staff-btn-secondary">Manage menu</a>
        <a href="{{ route('cashier.dashboard') }}" class="staff-btn-secondary">Cashier view</a>
    </div>
@endsection
