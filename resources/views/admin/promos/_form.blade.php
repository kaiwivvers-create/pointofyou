<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Image</label>
        <input type="file" id="image-{{ $modalPrefix }}" name="image" accept="image/*" onchange="handleImageUpload(this, '{{ $modalPrefix }}')" class="staff-input">
        
        <div id="imagePreview-{{ $modalPrefix }}" class="mt-3 hidden">
            <img id="previewImg-{{ $modalPrefix }}" src="" alt="Preview" class="w-full h-32 object-cover rounded-lg border border-slate-200">
        </div>
        
        <div id="cropButtonContainer-{{ $modalPrefix }}" class="mt-2 hidden">
            <button type="button" onclick="openCropModal('{{ $modalPrefix }}')" class="staff-btn-secondary text-sm">Crop Image</button>
        </div>
        
        <input type="hidden" id="croppedImageData-{{ $modalPrefix }}" name="cropped_image">
    </div>
    
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Title (optional)</label>
        <input type="text" name="title" value="{{ old('title', $promo->title ?? '') }}" placeholder="Promo title" autocomplete="off" maxlength="255" class="staff-input">
        @error('title', 'createPromo')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
        @error('title', 'editPromo')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Description (optional)</label>
        <textarea name="description" rows="3" placeholder="Promo description" maxlength="5000" class="staff-input">{{ old('description', $promo->description ?? '') }}</textarea>
        @error('description', 'createPromo')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
        @error('description', 'editPromo')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="border-t border-slate-200 pt-4 mt-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-slate-900">Promo Configuration</h3>
            <button type="button" onclick="clearPromoRules('{{ $modalPrefix }}')" class="text-xs text-red-600 hover:text-red-700 font-semibold">Clear All</button>
        </div>
        
        <div id="promoRulesContainer-{{ $modalPrefix }}">
            @php
                $rules = old('rules', $promo->rules ?? []);
                if (empty($rules)) {
                    $rules = [['buy_item_id' => '', 'get_item_id' => '', 'buy_quantity' => 1, 'get_quantity' => 1]];
                }
            @endphp
            @foreach ($rules as $index => $rule)
                <div class="promo-rule-row grid grid-cols-2 gap-4 mb-4 p-4 bg-slate-50 rounded-lg" data-index="{{ $index }}">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Buy Item</label>
                        <select name="rules[{{ $index }}][buy_item_id]" class="staff-input">
                            <option value="">Select item to buy</option>
                            @foreach (\App\Models\MenuItem::where('is_available', true)->orderBy('name')->get() as $item)
                                <option value="{{ $item->id }}" {{ ($rule['buy_item_id'] ?? '') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Buy Quantity</label>
                        <input type="number" name="rules[{{ $index }}][buy_quantity]" value="{{ $rule['buy_quantity'] ?? 1 }}" min="1" class="staff-input">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Get Item</label>
                        <select name="rules[{{ $index }}][get_item_id]" class="staff-input">
                            <option value="">Select item to get</option>
                            @foreach (\App\Models\MenuItem::where('is_available', true)->orderBy('name')->get() as $item)
                                <option value="{{ $item->id }}" {{ ($rule['get_item_id'] ?? '') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Get Quantity</label>
                        <input type="number" name="rules[{{ $index }}][get_quantity]" value="{{ $rule['get_quantity'] ?? 1 }}" min="1" class="staff-input">
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="flex gap-3 mt-2">
            <button type="button" onclick="addBuyItem('{{ $modalPrefix }}')" class="text-sm font-semibold text-amber-600 hover:text-amber-700">+ Add Buy Item</button>
            <button type="button" onclick="addGetItem('{{ $modalPrefix }}')" class="text-sm font-semibold text-amber-600 hover:text-amber-700">+ Add Get Item</button>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-4 mt-4">
        <h3 class="text-sm font-semibold text-slate-900 mb-3">Discount</h3>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Discount Type</label>
                <select name="discount_type" class="staff-input">
                    <option value="">No Discount</option>
                    <option value="percentage" {{ old('discount_type', $promo->discount_type ?? '') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                    <option value="fixed" {{ old('discount_type', $promo->discount_type ?? '') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                </select>
                @error('discount_type', 'createPromo')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
                @error('discount_type', 'editPromo')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Discount Value</label>
                <input type="number" name="discount_value" value="{{ old('discount_value', $promo->discount_value ?? '') }}" step="0.01" min="0" class="staff-input" placeholder="0.00">
                @error('discount_value', 'createPromo')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
                @error('discount_value', 'editPromo')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
    
    <div class="flex items-center gap-4 mt-4">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $promo->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
            <span class="text-sm font-medium text-slate-700">Active</span>
        </label>
        
        <div class="flex-1">
            <label class="block text-sm font-medium text-slate-700 mb-1">Order</label>
            <input type="number" name="order" value="{{ old('order', $promo->order ?? 0) }}" min="0" class="staff-input w-24">
            @error('order', 'createPromo')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
            @error('order', 'editPromo')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
