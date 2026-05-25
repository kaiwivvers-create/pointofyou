@extends('layouts.staff')

@section('title', 'Manager Dashboard')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Manager Dashboard</h1>
            <p class="staff-page-subtitle">Daily performance and operations.</p>
        </div>
    </div>

    <x-flash />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Today's Revenue</p>
            <p class="text-3xl font-bold text-slate-900">${{ number_format($todayRevenue, 2) }}</p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Today's Orders</p>
            <p class="text-3xl font-bold text-slate-900">{{ $todayOrders }}</p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Pending Orders</p>
            <p class="text-3xl font-bold text-slate-900">{{ $pendingOrders }}</p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Menu Items</p>
            <p class="text-3xl font-bold text-slate-900">{{ $menuCount }}</p>
        </div>
    </div>
@endsection
