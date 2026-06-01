@extends('layouts.staff')

@section('title', 'Edit staff user')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Edit staff user</h1>
            <p class="staff-page-subtitle">{{ $user->email }}</p>
        </div>
    </div>

    <x-flash />

    <form method="POST" action="{{ route('super-admin.users.update', $user) }}" class="staff-form-card max-w-md">
        @csrf
        @method('PUT')
        @include('super-admin.users._form', ['user' => $user, 'roles' => $roles])
        <div class="mt-8 flex flex-wrap gap-3">
            <button type="submit" class="staff-btn-primary">Save changes</button>
            <a href="{{ route('super-admin.users.index') }}" class="staff-btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
