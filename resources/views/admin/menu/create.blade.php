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
        @include('admin.menu._form', ['prefix' => 'add', 'products' => $products])
        <div class="mt-8 flex flex-wrap gap-3">
            <button type="submit" class="staff-btn-primary">Save item</button>
            <a href="{{ route('admin.menu.index') }}" class="staff-btn-secondary">Cancel</a>
        </div>
    </form>

    <script>
    document.querySelector('.add-mod-btn').addEventListener('click', function() {
        const container = document.getElementById('modifications-container-add');
        const modIndex = container.querySelectorAll('.modification-row').length;

        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 modification-row';
        div.innerHTML = `
            <input type="text" name="modifications[${modIndex}][name]" placeholder="Name (e.g. No Mayo)" required class="staff-input flex-1">
            <input type="number" step="0.01" min="0" name="modifications[${modIndex}][additional_price]" value="0" placeholder="+ Price ($)" required class="staff-input w-28">
            <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm px-2 py-1 text-xl font-bold">&times;</button>
        `;
        container.appendChild(div);
    });

    document.querySelector('.add-flavor-btn').addEventListener('click', function() {
        const container = document.getElementById('flavors-container-add');
        const flavorIndex = container.querySelectorAll('.flavor-row').length;

        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 flavor-row';
        div.innerHTML = `
            <input type="text" name="flavors[${flavorIndex}][name]" placeholder="Flavor name" required class="staff-input flex-1">
            <input type="number" step="0.01" min="0" name="flavors[${flavorIndex}][additional_price]" value="0" placeholder="+ Price ($)" required class="staff-input w-28">
            <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm px-2 py-1 text-xl font-bold">&times;</button>
        `;
        container.appendChild(div);
    });
    </script>
@endsection
