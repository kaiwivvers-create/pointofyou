@extends('layouts.staff')

@section('title', 'Edit Gift')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Edit Gift</h1>
            <p class="staff-page-subtitle">Edit gift or toy details.</p>
        </div>
    </div>

    <x-flash />

    <div class="staff-form-card">
        <form method="POST" action="{{ route('admin.gifts.update', $gift) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <div>
                    <label class="staff-label" for="name">Name</label>
                    <input type="text" id="name" name="name" class="staff-input" value="{{ old('name', $gift->name) }}" required autofocus>
                </div>

                <div>
                    <label class="staff-label" for="description">Description</label>
                    <textarea id="description" name="description" class="staff-input" rows="3">{{ old('description', $gift->description) }}</textarea>
                </div>

                <div>
                    <label class="staff-label" for="image">Image</label>
                    <input type="file" id="image" name="image" class="staff-input" accept="image/*">
                    @if ($gift->image)
                        <p class="mt-2 text-sm text-slate-600">Current: <img src="{{ asset('app-storage/' . $gift->image) }}" alt="{{ $gift->name }}" class="inline-block w-16 h-16 object-cover rounded border border-slate-200"></p>
                    @endif
                </div>

                <div>
                    <label class="staff-label" for="cost">Cost</label>
                    <input type="number" id="cost" name="cost" class="staff-input" step="0.01" min="0" value="{{ old('cost', $gift->cost) }}" required>
                </div>

                <div>
                    <label class="staff-label" for="stock_quantity">Stock Quantity</label>
                    <input type="number" id="stock_quantity" name="stock_quantity" class="staff-input" min="0" value="{{ old('stock_quantity', $gift->stock_quantity) }}" required>
                </div>

                <div>
                    <label class="staff-label" for="order">Order</label>
                    <input type="number" id="order" name="order" class="staff-input" value="{{ old('order', $gift->order) }}">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $gift->is_active) ? 'checked' : '' }}>
                    <label for="is_active" class="text-sm text-slate-700">Active</label>
                </div>

                <div class="flex gap-3 pt-4">
                    <a href="{{ route('admin.gifts.index') }}" class="staff-btn-secondary">Cancel</a>
                    <button type="submit" class="staff-btn-primary">Update Gift</button>
                </div>
            </div>
        </form>
    </div>
@endsection
