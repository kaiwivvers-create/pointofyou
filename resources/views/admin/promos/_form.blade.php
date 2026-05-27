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
        <input type="text" name="title" value="{{ $promo->title ?? '' }}" placeholder="Promo title" class="staff-input">
    </div>
    
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Description (optional)</label>
        <textarea name="description" rows="3" placeholder="Promo description" class="staff-input">{{ $promo->description ?? '' }}</textarea>
    </div>
    
    <div class="flex items-center gap-4">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" {{ ($promo->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
            <span class="text-sm font-medium text-slate-700">Active</span>
        </label>
        
        <div class="flex-1">
            <label class="block text-sm font-medium text-slate-700 mb-1">Order</label>
            <input type="number" name="order" value="{{ $promo->order ?? 0 }}" min="0" class="staff-input w-24">
        </div>
    </div>
</div>
