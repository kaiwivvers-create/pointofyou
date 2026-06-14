@extends('layouts.staff')

@section('title', 'Edit Packet')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Edit Packet</h1>
            <p class="staff-page-subtitle">Edit packet bundle details.</p>
        </div>
    </div>

    <x-flash />

    <div class="staff-form-card">
        <form method="POST" action="{{ route('admin.packets.update', $packet) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <div>
                    <label class="staff-label" for="name">Name</label>
                    <input type="text" id="name" name="name" class="staff-input" value="{{ old('name', $packet->name) }}" required autofocus>
                </div>

                <div>
                    <label class="staff-label" for="description">Description</label>
                    <textarea id="description" name="description" class="staff-input" rows="3">{{ old('description', $packet->description) }}</textarea>
                </div>

                <div>
                    <label class="staff-label" for="image">Image</label>
                    <input type="file" id="image" name="image" class="staff-input" accept="image/*">
                    @if ($packet->image)
                        <p class="mt-2 text-sm text-slate-600">Current: <img src="{{ asset('app-storage/' . $packet->image) }}" alt="{{ $packet->name }}" class="inline-block w-16 h-16 object-cover rounded border border-slate-200"></p>
                    @endif
                </div>

                <div>
                    <label class="staff-label" for="fixed_price">Fixed Price</label>
                    <input type="number" id="fixed_price" name="fixed_price" class="staff-input" step="0.01" min="0" value="{{ old('fixed_price', $packet->fixed_price) }}" required>
                </div>

                <div>
                    <label class="staff-label" for="order">Order</label>
                    <input type="number" id="order" name="order" class="staff-input" value="{{ old('order', $packet->order) }}">
                </div>

                <div>
                    <label class="staff-label">Items</label>
                    <div id="items-container" class="space-y-2">
                        @foreach ($packet->items as $index => $item)
                            <div class="item-row flex gap-2">
                                <select name="items[{{ $index }}][menu_item_id]" class="staff-input flex-1">
                                    <option value="">Select menu item</option>
                                    @foreach ($menuItems as $menuItem)
                                        <option value="{{ $menuItem->id }}" {{ $menuItem->id == $item->id ? 'selected' : '' }}>{{ $menuItem->name }} (${{ number_format($menuItem->price, 2) }})</option>
                                    @endforeach
                                </select>
                                <select name="items[{{ $index }}][gift_id]" class="staff-input flex-1">
                                    <option value="">Or select gift/toy</option>
                                    @foreach (\App\Models\Gift::where('is_active', true)->orderBy('name')->get() as $gift)
                                        <option value="{{ $gift->id }}" {{ $item->pivot->gift_id == $gift->id ? 'selected' : '' }}>{{ $gift->name }} (${{ number_format($gift->cost, 2) }})</option>
                                    @endforeach
                                </select>
                                <input type="number" name="items[{{ $index }}][quantity]" class="staff-input w-24" placeholder="Qty" min="1" value="{{ $item->pivot->quantity }}" required>
                                <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-700 px-2">Remove</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" onclick="addItemRow()" class="mt-2 text-sm font-medium text-slate-600 hover:text-slate-900">+ Add Item</button>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $packet->is_active) ? 'checked' : '' }}>
                    <label for="is_active" class="text-sm text-slate-700">Active</label>
                </div>

                <div class="flex gap-3 pt-4">
                    <a href="{{ route('admin.packets.index') }}" class="staff-btn-secondary">Cancel</a>
                    <button type="submit" class="staff-btn-primary">Update Packet</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        let itemCount = {{ $packet->items->count() }};
        const menuItems = @json($menuItems);
        const gifts = @json(\App\Models\Gift::where('is_active', true)->orderBy('name')->get(['id', 'name', 'cost'])->toArray());

        function addItemRow() {
            const container = document.getElementById('items-container');
            const newRow = document.createElement('div');
            newRow.className = 'item-row flex gap-2';
            newRow.innerHTML = `
                <select name="items[${itemCount}][menu_item_id]" class="staff-input flex-1">
                    <option value="">Select menu item</option>
                    ${menuItems.map(item => `<option value="${item.id}">${item.name} ($${item.price})</option>`).join('')}
                </select>
                <select name="items[${itemCount}][gift_id]" class="staff-input flex-1">
                    <option value="">Or select gift/toy</option>
                    ${gifts.map(gift => `<option value="${gift.id}">${gift.name} ($${gift.cost})</option>`).join('')}
                </select>
                <input type="number" name="items[${itemCount}][quantity]" class="staff-input w-24" placeholder="Qty" min="1" value="1" required>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-700 px-2">Remove</button>
            `;
            container.appendChild(newRow);
            itemCount++;
        }
    </script>
@endsection
