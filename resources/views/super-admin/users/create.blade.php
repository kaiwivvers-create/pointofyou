@extends('layouts.staff')

@section('title', 'Add staff user')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Add staff user</h1>
            <p class="staff-page-subtitle">Create a new login for your team.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-100">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('super-admin.users.store') }}" class="staff-form-card max-w-md">
        @csrf
        @include('super-admin.users._form', ['roles' => $roles])
        <div class="mt-8 flex flex-wrap gap-3">
            <button type="submit" class="staff-btn-primary">Create user</button>
            <a href="{{ route('super-admin.users.index') }}" class="staff-btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
