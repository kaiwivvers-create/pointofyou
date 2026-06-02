@extends('layouts.staff')

@section('title', 'Add Packet')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Add Packet</h1>
            <p class="staff-page-subtitle">Create a new packet bundle with a fixed price.</p>
        </div>
    </div>

    <x-flash />

    <div class="staff-form-card">
        <form method="POST" action="{{ route('admin.packets.store') }}" enctype="multipart/form-data">
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
                    <label class="staff-label" for="fixed_price">Fixed Price</label>
                    <input type="number" id="fixed_price" name="fixed_price" class="staff-input" step="0.01" min="0" required>
                </div>

                <div>
                    <label class="staff-label" for="order">Order</label>
                    <input type="number" id="order" name="order" class="staff-input" value="0">
                </div>

                <div>
                    <label class="staff-label">Items</label>
                    <div id="items-container" class="space-y-2">
                        <div class="item-row flex gap-2">
                            <select name="items[0][menu_item_id]" class="staff-input flex-1" required>
                                <option value="">Select item</option>
                                @foreach ($menuItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} (${{ number_format($item->price, 2) }})</option>
                                @endforeach
                            </select>
                            <input type="number" name="items[0][quantity]" class="staff-input w-24" placeholder="Qty" min="1" value="1" required>
                            <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-700 px-2">Remove</button>
                        </div>
                    </div>
                    <button type="button" onclick="addItemRow()" class="mt-2 text-sm font-medium text-slate-600 hover:text-slate-900">+ Add Item</button>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                    <label for="is_active" class="text-sm text-slate-700">Active</label>
                </div>

                <div class="flex gap-3 pt-4">
                    <a href="{{ route('admin.packets.index') }}" class="staff-btn-secondary">Cancel</a>
                    <button type="submit" class="staff-btn-primary">Create Packet</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        let itemCount = 1;
        const menuItems = @json($menuItems);

        function addItemRow() {
            const container = document.getElementById('items-container');
            const newRow = document.createElement('div');
            newRow.className = 'item-row flex gap-2';
            newRow.innerHTML = `
                <select name="items[${itemCount}][menu_item_id]" class="staff-input flex-1" required>
                    <option value="">Select item</option>
                    ${menuItems.map(item => `<option value="${item.id}">${item.name} ($${item.price})</option>`).join('')}
                </select>
                <input type="number" name="items[${itemCount}][quantity]" class="staff-input w-24" placeholder="Qty" min="1" value="1" required>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-700 px-2">Remove</button>
            `;
            container.appendChild(newRow);
            itemCount++;
        }
    </script>
@endsection
