@extends('layouts.staff')

@section('title', 'Gifts')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Gifts & Toys</h1>
            <p class="staff-page-subtitle">Manage extra gifts like CDs, toys, and other promotional items.</p>
        </div>
        <button onclick="openAddModal()" class="staff-btn-primary">Add Gift</button>
    </div>

    <x-flash />

    <div class="staff-table-wrap">
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Cost</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Order</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($gifts as $gift)
                        <tr>
                            <td>
                                @if ($gift->image)
                                    <img src="{{ asset('storage/' . $gift->image) }}" alt="{{ $gift->name }}" class="w-16 h-16 object-cover rounded-lg border border-slate-200">
                                @else
                                    <div class="w-16 h-16 bg-slate-100 rounded-lg border border-slate-200 flex items-center justify-center text-slate-400 text-xs">No image</div>
                                @endif
                            </td>
                            <td class="font-semibold text-slate-900">{{ $gift->name }}</td>
                            <td class="text-sm text-slate-600">{{ $gift->description ?? '-' }}</td>
                            <td class="text-sm text-slate-900">${{ number_format($gift->cost, 2) }}</td>
                            <td class="text-sm text-slate-900">{{ $gift->stock_quantity }}</td>
                            <td>
                                @if ($gift->is_active)
                                    <span class="staff-badge-green">Active</span>
                                @else
                                    <span class="staff-badge-muted">Inactive</span>
                                @endif
                            </td>
                            <td class="text-sm text-slate-600">{{ $gift->order }}</td>
                            <td class="text-right">
                                <button onclick="openEditModal({{ $gift->toJson() }})" class="staff-link">Edit</button>
                                <form method="POST" action="{{ route('admin.gifts.destroy', $gift) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this gift?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="staff-link-danger ml-2">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-500">No gifts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div id="addModalContent" class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto scale-95 opacity-0 transition-all duration-200">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-slate-900">Add Gift</h2>
                    <button onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('admin.gifts.store') }}" enctype="multipart/form-data">
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
                            <label class="staff-label" for="add-cost">Cost</label>
                            <input type="number" id="add-cost" name="cost" class="staff-input" step="0.01" min="0" required>
                        </div>
                        <div>
                            <label class="staff-label" for="add-stock_quantity">Stock Quantity</label>
                            <input type="number" id="add-stock_quantity" name="stock_quantity" class="staff-input" min="0" required>
                        </div>
                        <div>
                            <label class="staff-label" for="add-order">Order</label>
                            <input type="number" id="add-order" name="order" class="staff-input" value="0">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="add-is_active" name="is_active" value="1" checked>
                            <label for="add-is_active" class="text-sm text-slate-700">Active</label>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="button" onclick="closeAddModal()" class="staff-btn-secondary">Cancel</button>
                            <button type="submit" class="staff-btn-primary">Create Gift</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div id="editModalContent" class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto scale-95 opacity-0 transition-all duration-200">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-slate-900">Edit Gift</h2>
                    <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form id="editForm" method="POST" action="" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit-gift-id" name="gift_id">
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
                            <label class="staff-label" for="edit-cost">Cost</label>
                            <input type="number" id="edit-cost" name="cost" class="staff-input" step="0.01" min="0" required>
                        </div>
                        <div>
                            <label class="staff-label" for="edit-stock_quantity">Stock Quantity</label>
                            <input type="number" id="edit-stock_quantity" name="stock_quantity" class="staff-input" min="0" required>
                        </div>
                        <div>
                            <label class="staff-label" for="edit-order">Order</label>
                            <input type="number" id="edit-order" name="order" class="staff-input">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="edit-is_active" name="is_active" value="1">
                            <label for="edit-is_active" class="text-sm text-slate-700">Active</label>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="button" onclick="closeEditModal()" class="staff-btn-secondary">Cancel</button>
                            <button type="submit" class="staff-btn-primary">Update Gift</button>
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

        function openEditModal(gift) {
            const modal = document.getElementById('editModal');
            const content = document.getElementById('editModalContent');
            const form = document.getElementById('editForm');
            form.action = '{{ route('admin.gifts.update', ':id') }}'.replace(':id', gift.id);
            document.getElementById('edit-gift-id').value = gift.id;
            document.getElementById('edit-name').value = gift.name;
            document.getElementById('edit-description').value = gift.description || '';
            document.getElementById('edit-cost').value = gift.cost;
            document.getElementById('edit-stock_quantity').value = gift.stock_quantity;
            document.getElementById('edit-order').value = gift.order;
            document.getElementById('edit-is_active').checked = gift.is_active;
            
            if (gift.image) {
                document.getElementById('edit-image-preview').classList.remove('hidden');
                document.getElementById('edit-current-image').src = gift.image.startsWith('http') ? gift.image : '{{ asset('storage/') }}' + gift.image;
            } else {
                document.getElementById('edit-image-preview').classList.add('hidden');
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

        function handleImageUpload(input, prefix) {
            const preview = document.getElementById('imagePreview-' + prefix);
            const previewImg = document.getElementById('previewImg-' + prefix);
            const cropButton = document.getElementById('cropButtonContainer-' + prefix);
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.classList.remove('hidden');
                    cropButton.classList.remove('hidden');
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

                const canvas = cropper.getCroppedCanvas({
                    maxWidth: 4096,
                    maxHeight: 4096,
                });

                if (canvas) {
                    const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.9);
                    previewImg.src = croppedDataUrl;
                    hiddenInput.value = croppedDataUrl;
                    previewDiv.classList.remove('hidden');
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
    </script>
@endsection
