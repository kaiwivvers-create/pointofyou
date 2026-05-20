@extends('layouts.staff')

@section('title', 'Edit menu item')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Edit menu item</h1>
            <p class="staff-page-subtitle">{{ $menuItem->name }}</p>
        </div>
    </div>

    <x-flash />

    <form method="POST" action="{{ route('admin.menu.update', $menuItem) }}" class="staff-form-card">
        @csrf
        @method('PUT')
        @include('admin.menu._form', ['menuItem' => $menuItem])
        <div class="mt-8 flex flex-wrap gap-3">
            <button type="submit" class="staff-btn-primary">Update item</button>
            <a href="{{ route('admin.menu.index') }}" class="staff-btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
