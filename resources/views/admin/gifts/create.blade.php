@extends('layouts.staff')

@section('title', 'Add Gift')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Add Gift</h1>
            <p class="staff-page-subtitle">Add a new gift or toy to the inventory.</p>
        </div>
    </div>

    <x-flash />

    <div class="staff-form-card">
        <form method="POST" action="{{ route('admin.gifts.store') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="space-y-6">
                <div>
                    <label class="staff-label" for="name">Name</label>
                    <input type="text" id="name" name="name" class="staff-input" required autofocus>
                </div>

                <div>
                    <label class="staff-label" for="description">Description</label>
                    <textarea id="description" name="description" class="staff-input" rows="3"></textarea>
                </div>

                <div>
                    <label class="staff-label" for="image">Image</label>
                    <input type="file" id="image" name="image" class="staff-input" accept="image/*">
                </div>

                <div>
                    <label class="staff-label" for="cost">Cost</label>
                    <input type="number" id="cost" name="cost" class="staff-input" step="0.01" min="0" required>
                </div>

                <div>
                    <label class="staff-label" for="stock_quantity">Stock Quantity</label>
                    <input type="number" id="stock_quantity" name="stock_quantity" class="staff-input" min="0" required>
                </div>

                <div>
                    <label class="staff-label" for="order">Order</label>
                    <input type="number" id="order" name="order" class="staff-input" value="0">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                    <label for="is_active" class="text-sm text-slate-700">Active</label>
                </div>

                <div class="flex gap-3 pt-4">
                    <a href="{{ route('admin.gifts.index') }}" class="staff-btn-secondary">Cancel</a>
                    <button type="submit" class="staff-btn-primary">Create Gift</button>
                </div>
            </div>
        </form>
    </div>
@endsection
