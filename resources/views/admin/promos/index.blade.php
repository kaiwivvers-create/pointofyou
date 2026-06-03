@extends('layouts.staff')

@section('title', 'Promos')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Promos</h1>
            <p class="staff-page-subtitle">Manage promotional banners and buy/get discounts for menu pages.</p>
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
                        <th>Promo Details</th>
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
                            <td class="text-sm text-slate-600">
                                @if ($promo->rules && $promo->rules->count() > 0)
                                    @foreach ($promo->rules as $rule)
                                        @php
                                            $ruleText = '';
                                            if ($rule->buyItem) {
                                                $ruleText = 'Buy ' . $rule->buy_quantity . ' ' . $rule->buyItem->name;
                                            }
                                            if ($rule->getItem) {
                                                $ruleText .= ($ruleText ? ', ' : '') . 'Get ' . $rule->get_quantity . ' ' . $rule->getItem->name;
                                            }
                                        @endphp
                                        @if($ruleText)
                                            {{ $ruleText }}
                                            @if (!$loop->last)<br>@endif
                                        @endif
                                    @endforeach
                                    @if ($promo->discount_type)
                                        <br>({{ $promo->discount_type }}: {{ $promo->discount_value }})
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
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
                            <td colspan="6" class="py-16 text-center text-slate-500">No promos yet. Add your first promo!</td>
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
                <p class="text-sm text-slate-500 mt-1">Upload a promotional banner image and configure buy/get rules.</p>
            </div>
            <form method="POST" action="{{ route('admin.promos.store') }}" class="p-6" enctype="multipart/form-data" autocomplete="off">
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
                <input type="hidden" name="promo_id" id="editPromoId" value="">
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
        @php
            $promoData = $promos->map(function ($promo) {
                return [
                    'id' => $promo->id,
                    'image' => $promo->image,
                    'title' => $promo->title,
                    'description' => $promo->description,
                    'is_active' => $promo->is_active,
                    'order' => $promo->order,
                    'rules' => $promo->rules->toArray(),
                ];
            })->values()->toArray();
            $createPromoHasErrors = session('errors') ? session('errors')->getBag('createPromo')->any() : false;
            $editPromoHasErrors = session('errors') ? session('errors')->getBag('editPromo')->any() : false;
            $oldEditPromoId = old('promo_id');
            $oldPromoTitle = old('title', '');
            $oldPromoDescription = old('description', '');
            $oldPromoOrder = old('order', '');
            $oldPromoIsActive = old('is_active');
            $oldBuyItemId = old('buy_item_id');
            $oldGetItemId = old('get_item_id');
            $oldBuyQuantity = old('buy_quantity');
            $oldGetQuantity = old('get_quantity');
            $oldDiscountType = old('discount_type');
            $oldDiscountValue = old('discount_value');
            $oldRules = old('rules');
        @endphp

        function openAddModal() {
            resetAddForm();
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

        function resetAddForm() {
            const form = document.querySelector('#addModal form');
            if (!form) return;

            form.reset();
            const titleInput = form.querySelector('[name="title"]');
            const descriptionInput = form.querySelector('[name="description"]');
            const orderInput = form.querySelector('[name="order"]');
            const isActiveInput = form.querySelector('[name="is_active"]');
            const buyItemIdInput = form.querySelector('[name="buy_item_id"]');
            const getItemIdInput = form.querySelector('[name="get_item_id"]');
            const buyQuantityInput = form.querySelector('[name="buy_quantity"]');
            const getQuantityInput = form.querySelector('[name="get_quantity"]');
            const discountTypeInput = form.querySelector('[name="discount_type"]');
            const discountValueInput = form.querySelector('[name="discount_value"]');

            if (titleInput) titleInput.value = '';
            if (descriptionInput) descriptionInput.value = '';
            if (orderInput) orderInput.value = '0';
            if (isActiveInput) isActiveInput.checked = true;
            
            // Reset promo rules container
            const rulesContainer = document.getElementById('promoRulesContainer-add');
            if (rulesContainer) {
                rulesContainer.innerHTML = '';
            }

            document.getElementById('croppedImageData-add').value = '';
            const preview = document.getElementById('imagePreview-add');
            const previewImg = document.getElementById('previewImg-add');
            const cropButton = document.getElementById('cropButtonContainer-add');
            const fileInput = document.getElementById('image-add');

            if (preview) preview.classList.add('hidden');
            if (previewImg) previewImg.src = '';
            if (cropButton) cropButton.classList.add('hidden');
            if (fileInput) fileInput.value = '';
        }

        function openEditModal(promo) {
            const modal = document.getElementById('editModal');
            const content = document.getElementById('editModalContent');
            const form = document.getElementById('editForm');
            const storageBaseUrl = @json(asset('storage'));
            form.action = '{{ route('admin.promos.update', ':id') }}'.replace(':id', promo.id);
            document.getElementById('editPromoId').value = promo.id;
            form.querySelector('[name="title"]').value = promo.title || '';
            form.querySelector('[name="description"]').value = promo.description || '';
            form.querySelector('[name="is_active"]').checked = promo.is_active;
            form.querySelector('[name="order"]').value = promo.order;
            
            // Load promo rules
            const rulesContainer = document.getElementById('promoRulesContainer-edit');
            if (rulesContainer && promo.rules) {
                rulesContainer.innerHTML = '';
                const menuItems = @json(\App\Models\MenuItem::where('is_available', true)->orderBy('name')->get(['id', 'name'])->toArray());
                
                promo.rules.forEach((rule, index) => {
                    let options = '<option value="">Select item to buy</option>';
                    menuItems.forEach(item => {
                        options += `<option value="${item.id}" ${item.id === rule.buy_item_id ? 'selected' : ''}>${item.name}</option>`;
                    });
                    
                    let getOptions = '<option value="">Select item to get</option>';
                    menuItems.forEach(item => {
                        getOptions += `<option value="${item.id}" ${item.id === rule.get_item_id ? 'selected' : ''}>${item.name}</option>`;
                    });

                    const gifts = @json(\App\Models\Gift::where('is_active', true)->orderBy('name')->get(['id', 'name'])->toArray());
                    let giftOptions = '<option value="">Select gift/toy to get</option>';
                    gifts.forEach(gift => {
                        giftOptions += `<option value="${gift.id}" ${gift.id === rule.gift_id ? 'selected' : ''}>${gift.name}</option>`;
                    });
                    
                    const newRow = document.createElement('div');
                    newRow.className = 'promo-rule-row grid grid-cols-2 gap-4 mb-4 p-4 bg-slate-50 rounded-lg';
                    newRow.dataset.index = index;
                    newRow.innerHTML = `
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Buy Item</label>
                            <select name="rules[${index}][buy_item_id]" class="staff-input">${options}</select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Buy Quantity</label>
                            <input type="number" name="rules[${index}][buy_quantity]" value="${rule.buy_quantity || 1}" min="1" class="staff-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Get Item</label>
                            <select name="rules[${index}][get_item_id]" class="staff-input">${getOptions}</select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Or Get Gift/Toy</label>
                            <select name="rules[${index}][gift_id]" class="staff-input">${giftOptions}</select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Get Quantity</label>
                            <input type="number" name="rules[${index}][get_quantity]" value="${rule.get_quantity || 1}" min="1" class="staff-input">
                        </div>
                    `;
                    rulesContainer.appendChild(newRow);
                });
            }
            
            // Load discount fields
            if (promo.discount_type) {
                form.querySelector('[name="discount_type"]').value = promo.discount_type;
            }
            if (promo.discount_value) {
                form.querySelector('[name="discount_value"]').value = promo.discount_value;
            }

            // Show existing image
            if (promo.image) {
                const preview = document.getElementById('imagePreview-edit');
                const previewImg = document.getElementById('previewImg-edit');
                if (preview && previewImg) {
                    previewImg.src = `${storageBaseUrl}/${promo.image}`.replace(/\/+/g, '/').replace(':/', '://');
                    preview.classList.remove('hidden');
                }
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const createPromoHasErrors = @json($createPromoHasErrors);
            const editPromoHasErrors = @json($editPromoHasErrors);
            const oldEditPromoId = @json($oldEditPromoId);
            const promos = @json($promoData);

            if (createPromoHasErrors) {
                const modal = document.getElementById('addModal');
                const content = document.getElementById('addModalContent');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            } else if (editPromoHasErrors && oldEditPromoId) {
                const promo = promos.find(item => String(item.id) === String(oldEditPromoId));
                if (promo) {
                    openEditModal(promo);
                    const form = document.getElementById('editForm');
                    form.querySelector('[name="title"]').value = @json($oldPromoTitle) || promo.title || '';
                    form.querySelector('[name="description"]').value = @json($oldPromoDescription) || promo.description || '';
                    form.querySelector('[name="order"]').value = @json($oldPromoOrder) || promo.order;
                    form.querySelector('[name="buy_item_id"]').value = @json($oldBuyItemId) || promo.buy_item_id || '';
                    form.querySelector('[name="get_item_id"]').value = @json($oldGetItemId) || promo.get_item_id || '';
                    form.querySelector('[name="buy_quantity"]').value = @json($oldBuyQuantity) || promo.buy_quantity || 1;
                    form.querySelector('[name="get_quantity"]').value = @json($oldGetQuantity) || promo.get_quantity || 1;
                    form.querySelector('[name="discount_type"]').value = @json($oldDiscountType) || promo.discount_type || '';
                    form.querySelector('[name="discount_value"]').value = @json($oldDiscountValue) || promo.discount_value || '';
                    
                    // Reload promo rules with old values
                    const rulesContainer = document.getElementById('promoRulesContainer-edit');
                    if (rulesContainer) {
                        rulesContainer.innerHTML = '';
                        // Add rules from old input or promo
                        const oldRules = @json($oldRules ?? []);
                        const rulesToUse = oldRules.length > 0 ? oldRules : (promo.rules || []);
                        const menuItems = @json(\App\Models\MenuItem::where('is_available', true)->orderBy('name')->get(['id', 'name'])->toArray());
                        
                        rulesToUse.forEach((rule, index) => {
                            let options = '<option value="">Select item to buy</option>';
                            menuItems.forEach(item => {
                                options += `<option value="${item.id}" ${item.id === rule.buy_item_id ? 'selected' : ''}>${item.name}</option>`;
                            });
                            
                            let getOptions = '<option value="">Select item to get</option>';
                            menuItems.forEach(item => {
                                getOptions += `<option value="${item.id}" ${item.id === rule.get_item_id ? 'selected' : ''}>${item.name}</option>`;
                            });

                            const gifts = @json(\App\Models\Gift::where('is_active', true)->orderBy('name')->get(['id', 'name'])->toArray());
                            let giftOptions = '<option value="">Select gift/toy to get</option>';
                            gifts.forEach(gift => {
                                giftOptions += `<option value="${gift.id}" ${gift.id === rule.gift_id ? 'selected' : ''}>${gift.name}</option>`;
                            });
                            
                            const newRow = document.createElement('div');
                            newRow.className = 'promo-rule-row grid grid-cols-2 gap-4 mb-4 p-4 bg-slate-50 rounded-lg';
                            newRow.dataset.index = index;
                            newRow.innerHTML = `
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Buy Item</label>
                                    <select name="rules[${index}][buy_item_id]" class="staff-input">${options}</select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Buy Quantity</label>
                                    <input type="number" name="rules[${index}][buy_quantity]" value="${rule.buy_quantity || 1}" min="1" class="staff-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Get Item</label>
                                    <select name="rules[${index}][get_item_id]" class="staff-input">${getOptions}</select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Or Get Gift/Toy</label>
                                    <select name="rules[${index}][gift_id]" class="staff-input">${giftOptions}</select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Get Quantity</label>
                                    <input type="number" name="rules[${index}][get_quantity]" value="${rule.get_quantity || 1}" min="1" class="staff-input">
                                </div>
                            `;
                            rulesContainer.appendChild(newRow);
                        });
                    }
                    
                    // Reload discount fields
                    const oldDiscountType = @json(old('discount_type') ?? '');
                    const oldDiscountValue = @json(old('discount_value') ?? '');
                    if (oldDiscountType) {
                        form.querySelector('[name="discount_type"]').value = oldDiscountType;
                    } else if (promo.discount_type) {
                        form.querySelector('[name="discount_type"]').value = promo.discount_type;
                    }
                    if (oldDiscountValue) {
                        form.querySelector('[name="discount_value"]').value = oldDiscountValue;
                    } else if (promo.discount_value) {
                        form.querySelector('[name="discount_value"]').value = promo.discount_value;
                    }
                    const isActiveValue = @json($oldPromoIsActive);
                    if (isActiveValue !== null) {
                        form.querySelector('[name="is_active"]').checked = !!isActiveValue;
                    }
                }
            }
        });

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

        function addBuyItem(prefix) {
            const container = document.getElementById('promoRulesContainer-' + prefix);
            const ruleCount = container.querySelectorAll('.promo-rule-row').length;
            const menuItems = @json(\App\Models\MenuItem::where('is_available', true)->orderBy('name')->get(['id', 'name'])->toArray());

            let buyOptions = '<option value="">Select item to buy</option>';
            menuItems.forEach(item => {
                buyOptions += `<option value="${item.id}">${item.name}</option>`;
            });

            const newRow = document.createElement('div');
            newRow.className = 'promo-rule-row grid grid-cols-2 gap-4 mb-4 p-4 bg-slate-50 rounded-lg';
            newRow.dataset.index = ruleCount;
            newRow.innerHTML = `
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Buy Item</label>
                    <select name="rules[${ruleCount}][buy_item_id]" class="staff-input">${buyOptions}</select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Buy Quantity</label>
                    <input type="number" name="rules[${ruleCount}][buy_quantity]" value="1" min="1" class="staff-input">
                </div>
                <input type="hidden" name="rules[${ruleCount}][get_item_id]" value="">
                <input type="hidden" name="rules[${ruleCount}][gift_id]" value="">
                <input type="hidden" name="rules[${ruleCount}][get_quantity]" value="1">
            `;

            container.appendChild(newRow);
        }

        function addGetItem(prefix) {
            const container = document.getElementById('promoRulesContainer-' + prefix);
            const ruleCount = container.querySelectorAll('.promo-rule-row').length;
            const menuItems = @json(\App\Models\MenuItem::where('is_available', true)->orderBy('name')->get(['id', 'name'])->toArray());
            const gifts = @json(\App\Models\Gift::where('is_active', true)->orderBy('name')->get(['id', 'name'])->toArray());

            let getOptions = '<option value="">Select item to get</option>';
            menuItems.forEach(item => {
                getOptions += `<option value="${item.id}">${item.name}</option>`;
            });

            let giftOptions = '<option value="">Select gift/toy to get</option>';
            gifts.forEach(gift => {
                giftOptions += `<option value="${gift.id}">${gift.name}</option>`;
            });

            const newRow = document.createElement('div');
            newRow.className = 'promo-rule-row grid grid-cols-2 gap-4 mb-4 p-4 bg-slate-50 rounded-lg';
            newRow.dataset.index = ruleCount;
            newRow.innerHTML = `
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Get Item</label>
                    <select name="rules[${ruleCount}][get_item_id]" class="staff-input">${getOptions}</select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Or Get Gift/Toy</label>
                    <select name="rules[${ruleCount}][gift_id]" class="staff-input">${giftOptions}</select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Get Quantity</label>
                    <input type="number" name="rules[${ruleCount}][get_quantity]" value="1" min="1" class="staff-input">
                </div>
                <input type="hidden" name="rules[${ruleCount}][buy_item_id]" value="">
                <input type="hidden" name="rules[${ruleCount}][buy_quantity]" value="1">
            `;

            container.appendChild(newRow);
        }

        function clearPromoRules(prefix) {
            const container = document.getElementById('promoRulesContainer-' + prefix);
            if (container) {
                container.innerHTML = '';
            }
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
