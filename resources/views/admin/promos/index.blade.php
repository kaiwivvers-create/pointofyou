@extends('layouts.staff')

@section('title', 'Promos')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Promos</h1>
            <p class="staff-page-subtitle">Manage promotional banners for menu pages.</p>
        </div>
        <button onclick="openAddModal()" class="staff-btn-primary">Add Promo</button>
    </div>

    <x-flash />

    <div class="staff-table-wrap">
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Order</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($promos as $promo)
                        <tr>
                            <td>
                                @if ($promo->image)
                                    <img src="{{ asset('storage/' . $promo->image) }}" alt="{{ $promo->title }}" class="w-24 h-12 object-cover rounded-lg border border-slate-200">
                                @else
                                    <div class="w-24 h-12 bg-slate-100 rounded-lg border border-slate-200 flex items-center justify-center text-slate-400 text-xs">No image</div>
                                @endif
                            </td>
                            <td class="font-semibold text-slate-900">{{ $promo->title ?? '-' }}</td>
                            <td>
                                @if ($promo->is_active)
                                    <span class="staff-badge-green">Active</span>
                                @else
                                    <span class="staff-badge-muted">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $promo->order }}</td>
                            <td class="text-right space-x-4">
                                <button onclick="openEditModal({{ $promo->toJson() }})" class="staff-link">Edit</button>
                                <form method="POST" action="{{ route('admin.promos.destroy', $promo) }}" class="inline" onsubmit="return confirm('Delete this promo?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="staff-link-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center text-slate-500">No promos yet. Add your first promo!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0" id="addModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Add Promo</h2>
                <p class="text-sm text-slate-500 mt-1">Upload a promotional banner image.</p>
            </div>
            <form method="POST" action="{{ route('admin.promos.store') }}" class="p-6" enctype="multipart/form-data">
                @csrf
                @include('admin.promos._form', ['modalPrefix' => 'add'])
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeAddModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Save Promo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0" id="editModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Edit Promo</h2>
            </div>
            <form id="editForm" method="POST" class="p-6" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.promos._form', ['modalPrefix' => 'edit', 'promo' => null])
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeEditModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Update Promo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Crop Modal -->
    <div id="cropModal" class="fixed inset-0 bg-black/90 backdrop-blur-sm hidden items-center justify-center z-[10000] transition-opacity duration-200 overflow-y-auto">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 my-8 transform transition-all duration-200 scale-95 opacity-0" id="cropModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Crop Image</h2>
                <p class="text-sm text-slate-500 mt-1">Image will be cropped to landscape aspect ratio (16:6)</p>
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

        function openEditModal(promo) {
            const modal = document.getElementById('editModal');
            const content = document.getElementById('editModalContent');
            const form = document.getElementById('editForm');
            form.action = '/admin/promos/' + promo.id;
            form.querySelector('[name="title"]').value = promo.title || '';
            form.querySelector('[name="description"]').value = promo.description || '';
            form.querySelector('[name="is_active"]').checked = promo.is_active;
            form.querySelector('[name="order"]').value = promo.order;

            // Show existing image
            if (promo.image) {
                const preview = document.getElementById('imagePreview-edit');
                const previewImg = document.getElementById('previewImg-edit');
                previewImg.src = '{{ asset('storage/') }}' + promo.image;
                preview.classList.remove('hidden');
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
            
            if (fileInput.files && fileInput.files[0]) {
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
                                aspectRatio: 16 / 6, // Landscape aspect ratio for banners
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

                const canvas = cropper.getCroppedCanvas({
                    maxWidth: 4096,
                    maxHeight: 4096,
                });

                if (canvas) {
                    const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.9);
                    previewImg.src = croppedDataUrl;
                    hiddenInput.value = croppedDataUrl;
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
