@extends('layouts.staff')

@section('title', 'Owner Dashboard')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Owner Dashboard</h1>
            <p class="staff-page-subtitle">Overview of your cafe performance.</p>
        </div>
    </div>

    <x-flash />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Total Revenue</p>
            <p class="text-3xl font-bold text-slate-900">${{ number_format($totalRevenue, 2) }}</p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Total Orders</p>
            <p class="text-3xl font-bold text-slate-900">{{ $totalOrders }}</p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Menu Items</p>
            <p class="text-3xl font-bold text-slate-900">{{ $menuCount }}</p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Tables</p>
            <p class="text-3xl font-bold text-slate-900">{{ $tableCount }}</p>
        </div>
    </div>
@endsection
