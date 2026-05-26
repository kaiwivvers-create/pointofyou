@extends('layouts.staff')

@section('title', 'Menu')

@php
    $user = auth()->user();
    $userPermissions = [];
    if ($user) {
        $userPermissions = \App\Models\Permission::where('role', $user->role->value)
            ->get()
            ->keyBy('permission');
    }
    
    $can = function($permission, $action = 'view') use ($user, $userPermissions) {
        if (!$user) return false;
        if ($user->isSuperAdmin()) return true;
        $perm = $userPermissions->get($permission);
        if (!$perm) return false;
        return $action === 'edit' ? $perm->can_edit : $perm->can_view;
    };
@endphp

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Menu</h1>
            <p class="staff-page-subtitle">Add and edit food & drinks for table ordering.</p>
        </div>
        @if ($can('menu', 'edit'))
            <button onclick="openAddModal()" class="staff-btn-primary">Add item</button>
        @endif
    </div>

    <x-flash />

    <!-- Search and Filter -->
    <form method="GET" action="{{ route('admin.menu.index') }}" class="staff-card p-4 mb-6 flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search menu items..." class="staff-input">
        </div>
        <div class="sm:w-48">
            <select name="category" class="staff-input">
                <option value="">All Categories</option>
                <option value="food" {{ request('category') === 'food' ? 'selected' : '' }}>Food</option>
                <option value="drinks" {{ request('category') === 'drinks' ? 'selected' : '' }}>Drinks</option>
            </select>
        </div>
        <div class="sm:w-48">
            <select name="status" class="staff-input">
                <option value="">All Status</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                <option value="hidden" {{ request('status') === 'hidden' ? 'selected' : '' }}>Hidden</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="staff-btn-primary">Filter</button>
            @if (request('search') || request('category') || request('status'))
                <a href="{{ route('admin.menu.index') }}" class="staff-btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="staff-table-wrap">
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($menuItems as $item)
                        <tr>
                            <td>
                                @if ($item->image)
                                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-16 h-16 object-cover rounded-lg border border-slate-200">
                                @else
                                    <div class="w-16 h-16 bg-slate-100 rounded-lg border border-slate-200 flex items-center justify-center text-slate-400 text-xs">No image</div>
                                @endif
                            </td>
                            <td class="font-semibold text-slate-900">{{ $item->name }}</td>
                            <td><span class="capitalize">{{ $item->category }}</span></td>
                            <td class="font-semibold text-slate-900">{{ $item->formattedPrice() }}</td>
                            <td>
                                @if ($item->is_available)
                                    <span class="staff-badge-green">Available</span>
                                @else
                                    <span class="staff-badge-muted">Hidden</span>
                                @endif
                            </td>
                            <td class="text-right space-x-4">
                                @if ($can('menu', 'edit'))
                                    <button onclick="openEditModal({{ $item->toJson() }})" class="staff-link">Edit</button>
                                    <form method="POST" action="{{ route('admin.menu.destroy', $item) }}" class="inline" onsubmit="return confirm('Remove this item?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="staff-link-danger">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center text-slate-500">No menu items yet. Add your first item!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($menuItems->hasPages())
        <div class="mt-6 flex justify-center">
            {{ $menuItems->appends(request()->only(['search', 'category', 'status']))->links() }}
        </div>
    @endif

    <!-- Add Modal -->
    <div id="addModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0" id="addModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Add menu item</h2>
                <p class="text-sm text-slate-500 mt-1">New food or drink for customers to order.</p>
            </div>
            <form method="POST" action="{{ route('admin.menu.store') }}" class="p-6" enctype="multipart/form-data">
                @csrf
                @include('admin.menu._form', ['modalPrefix' => 'add'])
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeAddModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Save item</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0" id="editModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Edit menu item</h2>
            </div>
            <form id="editForm" method="POST" class="p-6" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.menu._form', ['modalPrefix' => 'edit', 'menuItem' => null])
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeEditModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Update item</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Crop Modal -->
    <div id="cropModal" class="fixed inset-0 bg-black/90 backdrop-blur-sm hidden items-center justify-center z-[10000] transition-opacity duration-200 overflow-y-auto">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 my-8 transform transition-all duration-200 scale-95 opacity-0" id="cropModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Crop Image</h2>
            </div>
            <div class="p-6">
                <div class="mb-4" style="max-height: 60vh; overflow: hidden;">
                    <img id="cropImage" src="" alt="Crop this image" style="max-width: 100%;">
                </div>
                <div class="flex gap-3 justify-end mt-6">
                    <button type="button" onclick="closeCropModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="button" onclick="applyCrop()" class="staff-btn-primary">Apply Crop</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let cropper = null;

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

        function openEditModal(item) {
            const modal = document.getElementById('editModal');
            const content = document.getElementById('editModalContent');
            const form = document.getElementById('editForm');
            form.action = '/admin/menu/' + item.id;
            form.querySelector('[name="name"]').value = item.name;
            form.querySelector('[name="description"]').value = item.description || '';
            form.querySelector('[name="category"]').value = item.category;
            form.querySelector('[name="price"]').value = item.price;
            form.querySelector('[name="is_available"]').checked = item.is_available;

            // Clear modifications
            const modContainer = document.getElementById('modifications-container');
            modContainer.innerHTML = '';

            // Load modifications if any
            if (item.modifications && item.modifications.length > 0) {
                item.modifications.forEach((mod, index) => {
                    addModificationRow(mod.name, mod.additional_price, mod.id);
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

        function addModificationRow(name = '', price = '', id = null) {
            const container = document.getElementById('modifications-container');
            const div = document.createElement('div');
            div.className = 'flex items-center gap-2 modification-row';
            div.innerHTML = `
                <input type="text" name="modifications[${container.children.length}][name]" value="${name}" placeholder="Name (e.g. No Mayo)" required class="staff-input flex-1">
                <input type="number" step="0.01" min="0" name="modifications[${container.children.length}][additional_price]" value="${price}" placeholder="+ Price ($)" required class="staff-input w-28">
                <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm px-2 py-1 text-xl font-bold">&times;</button>
            `;
            container.appendChild(div);
        }

        // Image cropping functions
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
            
            // Use the original file instead of the preview data URL
            if (fileInput.files && fileInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    cropImage.src = e.target.result;
                    cropModal.dataset.prefix = prefix; // Store which modal opened the crop modal
                    cropModal.classList.remove('hidden');
                    cropModal.classList.add('flex');
                    
                    // Wait for image to load before initializing cropper
                    cropImage.onload = function() {
                        setTimeout(() => {
                            cropContent.classList.remove('scale-95', 'opacity-0');
                            cropContent.classList.add('scale-100', 'opacity-100');
                            
                            // Initialize cropper
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

                // Get the cropped canvas
                const canvas = cropper.getCroppedCanvas({
                    maxWidth: 4096,
                    maxHeight: 4096,
                });

                if (canvas) {
                    // Convert canvas to data URL
                    const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.9);
                    previewImg.src = croppedDataUrl;
                    hiddenInput.value = croppedDataUrl;
                    console.log('Image cropped successfully');
                } else {
                    console.error('Failed to crop image');
                    // Fallback to original if cropping fails
                    hiddenInput.value = previewImg.src;
                }

                closeCropModal();
            }
        }

        // Close modals on backdrop click
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
