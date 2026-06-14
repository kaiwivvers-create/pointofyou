@extends('layouts.staff')

@section('title', 'Packets')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Packets</h1>
            <p class="staff-page-subtitle">Manage packet bundles (like food, drinks, etc.) with fixed prices.</p>
        </div>
        <button onclick="openAddModal()" class="staff-btn-primary">Add Packet</button>
    </div>

    <x-flash />

    <div class="staff-table-wrap">
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Fixed Price</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Order</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="packets-list">
                    @forelse ($packets as $packet)
                        <tr data-id="{{ $packet->id }}">
                            <td>
                                <div class="cursor-not-allowed text-slate-300 p-1 drag-handle opacity-50">
                                    <div class="flex items-center gap-1">
                                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                        </svg>
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($packet->image)
                                    @php
                                        $imagePath = $packet->image;
                                        // Ensure path starts with app-storage/
                                        if (!str_starts_with($imagePath, 'app-storage/')) {
                                            $imagePath = 'app-storage/' . $imagePath;
                                        }
                                    @endphp
                                    <img src="{{ asset($imagePath) }}" alt="{{ $packet->name }}" class="w-16 h-16 object-cover rounded-lg border border-slate-200" onerror="console.error('Image failed to load:', this.src)">
                                @else
                                    <div class="w-16 h-16 bg-slate-100 rounded-lg border border-slate-200 flex items-center justify-center text-slate-400 text-xs">No image</div>
                                @endif
                            </td>
                            <td class="font-semibold text-slate-900">{{ $packet->name }}</td>
                            <td class="text-sm text-slate-600">{{ $packet->description ?? '-' }}</td>
                            <td class="text-sm text-slate-900">${{ number_format($packet->fixed_price, 2) }}</td>
                            <td class="text-sm text-slate-600">
                                @php
                                    $hasItems = $packet->items && $packet->items->count() > 0;
                                @endphp
                                @if ($hasItems)
                                    <div class="max-w-xs">
                                        @foreach ($packet->items as $item)
                                            @if ($item->pivot->gift_id)
                                                @php
                                                    $gift = \App\Models\Gift::find($item->pivot->gift_id);
                                                @endphp
                                                @if ($gift)
                                                    <div class="text-xs">{{ $gift->name }} (Gift) x{{ $item->pivot->quantity ?? 1 }}</div>
                                                @endif
                                            @else
                                                <div class="text-xs">{{ $item->name }} x{{ $item->pivot->quantity ?? 1 }}</div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400 italic font-bold">NO ITEMS</span>
                                @endif
                            </td>
                            <td>
                                @if ($packet->is_active)
                                    <span class="staff-badge-green">Active</span>
                                @else
                                    <span class="staff-badge-muted">Inactive</span>
                                @endif
                            </td>
                            <td class="text-sm text-slate-600">{{ $packet->order }}</td>
                            <td class="text-right">
                                <button onclick="openEditModal({{ $packet->toJson() }})" class="staff-link">Edit</button>
                                <form method="POST" action="{{ route('admin.packets.destroy', $packet) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this packet?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="staff-link-danger ml-2">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-500">No packets found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div id="addModalContent" class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto scale-95 opacity-0 transition-all duration-200">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-slate-900">Add Packet</h2>
                    <button onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('admin.packets.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="staff-label" for="add-name">Name</label>
                            <input type="text" id="add-name" name="name" class="staff-input" required autofocus>
                        </div>
                        <div>
                            <label class="staff-label" for="add-description">Description</label>
                            <textarea id="add-description" name="description" class="staff-input" rows="3"></textarea>
                        </div>
                        <div>
                            <label class="staff-label" for="image-add">Image</label>
                            <input type="file" id="image-add" name="image" class="staff-input" accept="image/*" onchange="handleImageUpload(this, 'add')">
                            
                            <div id="imagePreview-add" class="mt-3 hidden">
                                <img id="previewImg-add" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg border border-slate-200">
                            </div>
                            
                            <div id="cropButtonContainer-add" class="mt-2 hidden">
                                <button type="button" onclick="openCropModal('add')" class="staff-btn-secondary text-sm">Crop Image</button>
                            </div>
                            
                            <input type="hidden" id="croppedImageData-add" name="cropped_image">
                        </div>
                        <div>
                            <label class="staff-label" for="add-fixed_price">Fixed Price</label>
                            <input type="number" id="add-fixed_price" name="fixed_price" class="staff-input" step="0.01" min="0" required>
                        </div>
                        <div>
                            <label class="staff-label" for="add-order">Order</label>
                            <input type="number" id="add-order" name="order" class="staff-input" value="0">
                        </div>
                        <div>
                            <label class="staff-label">Items</label>
                            <div id="add-items-container" class="space-y-2">
                                <div class="item-row flex gap-2">
                                    <select name="items[0][menu_item_id]" class="staff-input flex-1" required>
                                        <option value="">Select item</option>
                                        @foreach ($menuItems as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }} (${{ number_format((float)($item->price ?? 0), 2) }})</option>
                                        @endforeach
                                    </select>
                                    <input type="number" name="items[0][quantity]" class="staff-input w-24" placeholder="Qty" min="1" value="1" required>
                                    <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-700 px-2">Remove</button>
                                </div>
                            </div>
                            <button type="button" onclick="addAddItemRow()" class="mt-2 text-sm font-medium text-slate-600 hover:text-slate-900">+ Add Item</button>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="add-is_active" name="is_active" value="1" checked>
                            <label for="add-is_active" class="text-sm text-slate-700">Active</label>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="button" onclick="closeAddModal()" class="staff-btn-secondary">Cancel</button>
                            <button type="submit" class="staff-btn-primary">Create Packet</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div id="editModalContent" class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto scale-95 opacity-0 transition-all duration-200">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-slate-900">Edit Packet</h2>
                    <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form id="editForm" method="POST" action="" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit-packet-id" name="packet_id">
                    <div class="space-y-4">
                        <div>
                            <label class="staff-label" for="edit-name">Name</label>
                            <input type="text" id="edit-name" name="name" class="staff-input" required>
                        </div>
                        <div>
                            <label class="staff-label" for="edit-description">Description</label>
                            <textarea id="edit-description" name="description" class="staff-input" rows="3"></textarea>
                        </div>
                        <div>
                            <label class="staff-label" for="image-edit">Image</label>
                            <input type="file" id="image-edit" name="image" class="staff-input" accept="image/*" onchange="handleImageUpload(this, 'edit')">
                            
                            <div id="imagePreview-edit" class="mt-3 hidden">
                                <img id="previewImg-edit" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg border border-slate-200">
                            </div>
                            
                            <div id="cropButtonContainer-edit" class="mt-2 hidden">
                                <button type="button" onclick="openCropModal('edit')" class="staff-btn-secondary text-sm">Crop Image</button>
                            </div>
                            
                            <input type="hidden" id="croppedImageData-edit" name="cropped_image">
                            
                            <div id="edit-image-preview" class="mt-2 hidden">
                                <p class="text-sm text-slate-600">Current: <img id="edit-current-image" src="" alt="" class="inline-block w-16 h-16 object-cover rounded border border-slate-200"></p>
                            </div>
                        </div>
                        <div>
                            <label class="staff-label" for="edit-fixed_price">Fixed Price</label>
                            <input type="number" id="edit-fixed_price" name="fixed_price" class="staff-input" step="0.01" min="0" required>
                        </div>
                        <div>
                            <label class="staff-label" for="edit-order">Order</label>
                            <input type="number" id="edit-order" name="order" class="staff-input">
                        </div>
                        <div>
                            <label class="staff-label">Items</label>
                            <div id="edit-items-container" class="space-y-2"></div>
                            <button type="button" onclick="addEditItemRow()" class="mt-2 text-sm font-medium text-slate-600 hover:text-slate-900">+ Add Item</button>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="edit-is_active" name="is_active" value="1">
                            <label for="edit-is_active" class="text-sm text-slate-700">Active</label>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="button" onclick="closeEditModal()" class="staff-btn-secondary">Cancel</button>
                            <button type="submit" class="staff-btn-primary">Update Packet</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Crop Modal -->
    <div id="cropModal" class="hidden fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4">
        <div id="cropModalContent" class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto scale-95 opacity-0 transition-all duration-200">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-slate-900">Crop Image</h2>
                    <button onclick="closeCropModal()" class="text-slate-400 hover:text-slate-600">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="relative bg-slate-100 rounded-lg overflow-hidden" style="min-height: 400px;">
                    <img id="cropImage" src="" alt="Crop" class="max-w-full">
                </div>
                <div class="flex gap-3 mt-6 justify-end">
                    <button type="button" onclick="closeCropModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="button" onclick="applyCrop()" class="staff-btn-primary">Apply Crop</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Debug: Check if menuItems is defined
        console.log('Script loaded');
        console.log('Menu items:', @json($menuItems));
        
        const menuItems = @json($menuItems);
        console.log('Menu items loaded:', menuItems.length, menuItems);
        console.log('First item:', menuItems[0]);
        console.log('First item price:', menuItems[0]?.price, typeof menuItems[0]?.price);
        
        let addItemCount = 1;
        let editItemCount = 0;

        function openAddModal() {
            const modal = document.getElementById('addModal');
            const content = document.getElementById('addModalContent');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeAddModal() {
            const modal = document.getElementById('addModal');
            const content = document.getElementById('addModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        function addAddItemRow() {
            const container = document.getElementById('add-items-container');
            const newRow = document.createElement('div');
            newRow.className = 'item-row flex gap-2';
            
            // Debug: Check if menuItems is available
            console.log('addAddItemRow called, menuItems:', menuItems);
            
            const itemOptions = menuItems.map(item => {
                const price = parseFloat(item.price);
                console.log('Item:', item.name, 'Price:', item.price, 'Type:', typeof item.price, 'Parsed:', price, 'Type of parsed:', typeof price);
                const formattedPrice = isNaN(price) ? '0.00' : price.toFixed(2);
                return `<option value="${item.id}">${item.name} - $${formattedPrice}</option>`;
            }).join('');
            
            console.log('Item options HTML:', itemOptions.substring(0, 200));
            
            newRow.innerHTML = `
                <select name="items[${addItemCount}][menu_item_id]" class="staff-input flex-1" required>
                    <option value="">Select item</option>
                    ${itemOptions}
                </select>
                <input type="number" name="items[${addItemCount}][quantity]" class="staff-input w-24" placeholder="Qty" min="1" value="1" required>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-700 px-2">Remove</button>
            `;
            container.appendChild(newRow);
            addItemCount++;
        }

        function openEditModal(packet) {
            const modal = document.getElementById('editModal');
            const content = document.getElementById('editModalContent');
            const form = document.getElementById('editForm');
            form.action = '{{ route('admin.packets.update', ':id') }}'.replace(':id', packet.id);
            document.getElementById('edit-packet-id').value = packet.id;
            document.getElementById('edit-name').value = packet.name;
            document.getElementById('edit-description').value = packet.description || '';
            document.getElementById('edit-fixed_price').value = packet.fixed_price;
            document.getElementById('edit-order').value = packet.order;
            document.getElementById('edit-is_active').checked = packet.is_active;
            
            if (packet.image) {
                document.getElementById('edit-image-preview').classList.remove('hidden');
                let imagePath = packet.image;
                if (!imagePath.startsWith('http')) {
                    let cleanPath = imagePath.replace(/^\/?app-storage\//, '').replace(/^\/+/, '');
                    document.getElementById('edit-current-image').src = '{{ asset('app-storage') }}/' + cleanPath;
                } else {
                    document.getElementById('edit-current-image').src = imagePath;
                }
            } else {
                document.getElementById('edit-image-preview').classList.add('hidden');
            }
            
            // Load items
            const container = document.getElementById('edit-items-container');
            container.innerHTML = '';
            editItemCount = 0;
            
            if (packet.items && packet.items.length > 0) {
                packet.items.forEach(item => {
                    const newRow = document.createElement('div');
                    newRow.className = 'item-row flex gap-2';
                    const itemOptions = menuItems.map(mi => {
                        const price = parseFloat(mi.price) || 0;
                        return `<option value="${mi.id}" ${mi.id === item.id ? 'selected' : ''}>${mi.name} - $${price.toFixed(2)}</option>`;
                    }).join('');
                    newRow.innerHTML = `
                        <select name="items[${editItemCount}][menu_item_id]" class="staff-input flex-1" required>
                            <option value="">Select item</option>
                            ${itemOptions}
                        </select>
                        <input type="number" name="items[${editItemCount}][quantity]" class="staff-input w-24" placeholder="Qty" min="1" value="${item.pivot.quantity}" required>
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-700 px-2">Remove</button>
                    `;
                    container.appendChild(newRow);
                    editItemCount++;
                });
            }
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            const content = document.getElementById('editModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        function addEditItemRow() {
            const container = document.getElementById('edit-items-container');
            const newRow = document.createElement('div');
            newRow.className = 'item-row flex gap-2';
            const itemOptions = menuItems.map(item => {
                const price = parseFloat(item.price) || 0;
                return `<option value="${item.id}">${item.name} - $${price.toFixed(2)}</option>`;
            }).join('');
            newRow.innerHTML = `
                <select name="items[${editItemCount}][menu_item_id]" class="staff-input flex-1" required>
                    <option value="">Select item</option>
                    ${itemOptions}
                </select>
                <input type="number" name="items[${editItemCount}][quantity]" class="staff-input w-24" placeholder="Qty" min="1" value="1" required>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-700 px-2">Remove</button>
            `;
            container.appendChild(newRow);
            editItemCount++;
        }

        function handleImageUpload(input, prefix) {
            const preview = document.getElementById('imagePreview-' + prefix);
            const previewImg = document.getElementById('previewImg-' + prefix);
            const cropButton = document.getElementById('cropButtonContainer-' + prefix);
            
            console.log('handleImageUpload called for prefix:', prefix);
            console.log('Input files:', input.files);
            console.log('Preview element:', preview);
            console.log('PreviewImg element:', previewImg);
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    console.log('FileReader onload, result length:', e.target.result.length);
                    previewImg.src = e.target.result;
                    previewImg.onload = function() {
                        console.log('PreviewImg loaded');
                        preview.classList.remove('hidden');
                        cropButton.classList.remove('hidden');
                        console.log('Preview shown for prefix:', prefix);
                    };
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.classList.add('hidden');
                cropButton.classList.add('hidden');
            }
        }

        function openCropModal(prefix) {
            const fileInput = document.getElementById('image-' + prefix);
            const previewImg = document.getElementById('previewImg-' + prefix);
            const cropModal = document.getElementById('cropModal');
            const cropContent = document.getElementById('cropModalContent');
            const cropImage = document.getElementById('cropImage');
            
            if (!fileInput || !fileInput.files || !fileInput.files[0]) {
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                cropImage.src = e.target.result;
                cropModal.dataset.prefix = prefix;
                cropModal.classList.remove('hidden');
                cropModal.classList.add('flex');
                
                cropImage.onload = function() {
                    setTimeout(() => {
                        cropContent.classList.remove('scale-95', 'opacity-0');
                        cropContent.classList.add('scale-100', 'opacity-100');
                        
                        if (cropper) {
                            cropper.destroy();
                        }
                        cropper = new Cropper(cropImage, {
                            aspectRatio: 1,
                            viewMode: 1,
                            autoCropArea: 0.8,
                            movable: true,
                            zoomable: true,
                            scalable: true,
                            rotatable: true,
                            responsive: true,
                            restore: false,
                            checkCrossOrigin: false,
                            dragMode: 'move',
                        });
                    }, 100);
                };
            };
            reader.readAsDataURL(fileInput.files[0]);
        }

        function closeCropModal() {
            const modal = document.getElementById('cropModal');
            const content = document.getElementById('cropModalContent');
            
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        function applyCrop() {
            if (cropper) {
                const prefix = document.getElementById('cropModal').dataset.prefix || 'add';
                const previewImg = document.getElementById('previewImg-' + prefix);
                const hiddenInput = document.getElementById('croppedImageData-' + prefix);
                const previewDiv = document.getElementById('imagePreview-' + prefix);

                console.log('applyCrop called for prefix:', prefix);

                const canvas = cropper.getCroppedCanvas({
                    maxWidth: 4096,
                    maxHeight: 4096,
                });

                if (canvas) {
                    const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.9);
                    console.log('Cropped data URL length:', croppedDataUrl.length);
                    previewImg.src = croppedDataUrl;
                    hiddenInput.value = croppedDataUrl;
                    previewDiv.classList.remove('hidden');
                    console.log('Preview div shown after crop');
                } else {
                    hiddenInput.value = previewImg.src;
                }

                closeCropModal();
            }
        }

        document.getElementById('addModal').addEventListener('click', function(e) {
            if (e.target === this) closeAddModal();
        });

        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });

        document.getElementById('cropModal').addEventListener('click', function(e) {
            if (e.target === this) closeCropModal();
        });

        // Initialize sortable for packets (disabled - packets are locked to top position)
        const packetsList = document.getElementById('packets-list');
        if (packetsList) {
            // Packets reordering is disabled - they always appear at the top
            // The drag handle shows a lock icon to indicate this
        }
    </script>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
@endpush
@endsection
