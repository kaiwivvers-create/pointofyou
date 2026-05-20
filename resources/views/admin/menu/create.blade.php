@extends('layouts.staff')

@section('title', 'Add menu item')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Add menu item</h1>
            <p class="staff-page-subtitle">New food or drink for customers to order.</p>
        </div>
    </div>

    <x-flash />

    <form method="POST" action="{{ route('admin.menu.store') }}" class="staff-form-card">
        @csrf
        @include('admin.menu._form')
        <div class="mt-8 flex flex-wrap gap-3">
            <button type="submit" class="staff-btn-primary">Save item</button>
            <a href="{{ route('admin.menu.index') }}" class="staff-btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
