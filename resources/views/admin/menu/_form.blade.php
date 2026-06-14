@php
    $menuItem = $menuItem ?? null;
    $isEdit = $menuItem !== null;
    $prefix = $modalPrefix ?? 'add';
@endphp

<div class="space-y-5">
    <div>
        <label for="image-{{ $prefix }}" class="staff-label">Image</label>
        <div class="mt-2">
            <input type="file" id="image-{{ $prefix }}" name="image" accept="image/*" class="staff-input" onchange="handleImageUpload(this, '{{ $prefix }}')">
            @if ($isEdit && $menuItem?->image)
                <div class="mt-2">
                    <p class="text-xs text-slate-500 mb-1">Current image:</p>
                    <img src="{{ asset('app-storage/' . $menuItem->image) }}" alt="Current image" class="w-32 h-32 object-cover rounded-lg border border-slate-200">
                </div>
            @endif
            <div id="imagePreview-{{ $prefix }}" class="mt-2 hidden">
                <p class="text-xs text-slate-500 mb-1">Preview:</p>
                <img id="previewImg-{{ $prefix }}" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg border border-slate-200">
            </div>
            <div id="cropButtonContainer-{{ $prefix }}" class="mt-2 hidden">
                <button type="button" onclick="openCropModal('{{ $prefix }}')" class="staff-btn-secondary text-xs px-3 py-1.5 whitespace-nowrap">Crop Image</button>
            </div>
            <input type="hidden" id="croppedImageData-{{ $prefix }}" name="cropped_image">
        </div>
    </div>
    <div>
        <label for="name" class="staff-label">Name</label>
        <input id="name" name="name" type="text" required maxlength="255" value="{{ old('name', $menuItem?->name) }}" class="staff-input max-w-md">
    </div>
    <div>
        <label for="description" class="staff-label">Description</label>
        <textarea id="description" name="description" rows="2" maxlength="5000" class="staff-input max-w-md">{{ old('description', $menuItem?->description) }}</textarea>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-xl">
        <div>
            <label for="category" class="staff-label">Category</label>
            <select id="category" name="category" required class="staff-input">
                @foreach (['food', 'drinks', 'pastry'] as $cat)
                    <option value="{{ $cat }}" @selected(old('category', $menuItem?->category ?? 'food') === $cat)>{{ ucfirst($cat) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="price" class="staff-label">Price ($)</label>
            <input id="price" name="price" type="number" step="0.01" min="0" required value="{{ old('price', $menuItem?->price) }}" class="staff-input">
        </div>
    </div>
    <label class="flex items-center gap-2.5 cursor-pointer select-none">
        <input type="hidden" name="is_available" value="0">
        <input type="checkbox" name="is_available" value="1" class="size-4 rounded border-slate-300 text-slate-900 focus:ring-slate-300"
            @if($isEdit && $menuItem->is_available === false) 
            @else 
                checked 
            @endif>
        <span class="text-sm text-slate-600">Available on menu</span>
    </label>
</div>

<div class="border-t border-slate-200 pt-5 mt-5">
    <h3 class="text-sm font-medium text-slate-800 mb-2">Customizations / Add-ons</h3>
    <p class="text-xs text-slate-500 mb-4">Add options like "No Pickles" (price: 0) or "Extra Cheese" (price: 1.50).</p>
    
    <div id="modifications-container-{{ $prefix }}" class="space-y-3">
        @if(old('modifications'))
            @foreach(old('modifications') as $index => $mod)
                <div class="flex items-center gap-2 modification-row">
                    <input type="text" name="modifications[{{ $index }}][name]" value="{{ $mod['name'] }}" placeholder="Name (e.g. No Mayo)" required maxlength="255" class="staff-input flex-1">
                    <input type="number" step="0.01" min="0" name="modifications[{{ $index }}][additional_price]" value="{{ $mod['additional_price'] }}" placeholder="+ Price ($)" required class="staff-input w-28">
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm px-2 py-1 remove-mod text-xl font-bold">&times;</button>
                </div>
            @endforeach
        @elseif($menuItem?->modifications)
            @foreach($menuItem->modifications as $index => $mod)
                <div class="flex items-center gap-2 modification-row">
                    <input type="text" name="modifications[{{ $index }}][name]" value="{{ $mod->name }}" placeholder="Name (e.g. No Mayo)" required maxlength="255" class="staff-input flex-1">
                    <input type="number" step="0.01" min="0" name="modifications[{{ $index }}][additional_price]" value="{{ $mod->additional_price }}" placeholder="+ Price ($)" required class="staff-input w-28">
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm px-2 py-1 remove-mod text-xl font-bold">&times;</button>
                </div>
            @endforeach
        @endif
    </div>
    
    <button type="button" class="add-mod-btn mt-3 text-sm text-slate-700 hover:text-slate-900 font-medium">+ Add customization</button>
</div>

<div class="border-t border-slate-200 pt-5 mt-5">
    <h3 class="text-sm font-medium text-slate-800 mb-2">Flavors</h3>
    <p class="text-xs text-slate-500 mb-4">Add flavor options (customer can only select one). E.g., "Chocolate", "Vanilla", "Strawberry".</p>

    <div id="flavors-container-{{ $prefix }}" class="space-y-3">
        @if(old('flavors'))
            @foreach(old('flavors') as $index => $flavor)
                <div class="flex items-center gap-2 flavor-row">
                    <input type="text" name="flavors[{{ $index }}][name]" value="{{ $flavor['name'] }}" placeholder="Flavor name" required maxlength="255" class="staff-input flex-1">
                    <input type="number" step="0.01" min="0" name="flavors[{{ $index }}][additional_price]" value="{{ $flavor['additional_price'] }}" placeholder="+ Price ($)" required class="staff-input w-28">
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm px-2 py-1 remove-flavor text-xl font-bold">&times;</button>
                </div>
            @endforeach
        @elseif($menuItem?->flavors)
            @foreach($menuItem->flavors as $index => $flavor)
                <div class="flex items-center gap-2 flavor-row">
                    <input type="text" name="flavors[{{ $index }}][name]" value="{{ $flavor->name }}" placeholder="Flavor name" required maxlength="255" class="staff-input flex-1">
                    <input type="number" step="0.01" min="0" name="flavors[{{ $index }}][additional_price]" value="{{ $flavor->additional_price }}" placeholder="+ Price ($)" required class="staff-input w-28">
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm px-2 py-1 remove-flavor text-xl font-bold">&times;</button>
                </div>
            @endforeach
        @endif
    </div>

    <button type="button" class="add-flavor-btn mt-3 text-sm text-slate-700 hover:text-slate-900 font-medium">+ Add flavor</button>
</div>

<div class="border-t border-slate-200 pt-5 mt-5">
    <h3 class="text-sm font-medium text-slate-800 mb-2">Ingredients</h3>
    <p class="text-xs text-slate-500 mb-4">Select ingredients and quantities needed to make this item. Inventory will be automatically deducted when sold.</p>

    @if(isset($products) && $products->isNotEmpty())
        @php
            $groupedProducts = $products->groupBy(function($product) {
                return $product->category ? $product->category->name : 'Uncategorized';
            });
            $existingIngredients = $menuItem?->ingredients?->pluck('pivot.quantity', 'id') ?? [];
            $ingredientIndex = 0;
        @endphp

        @foreach($groupedProducts as $categoryName => $categoryProducts)
            <div class="mb-4">
                <h4 class="text-xs font-medium text-slate-700 mb-2">{{ $categoryName }}</h4>
                <div class="space-y-2">
                    @foreach($categoryProducts as $product)
                        <div class="flex items-center gap-3 p-2 bg-slate-50 rounded-lg">
                            <input type="checkbox" name="ingredients[{{ $ingredientIndex }}][product_id]" value="{{ $product->id }}"
                                id="ingredient-{{ $product->id }}-{{ $prefix }}"
                                @isset($menuItem)
                                    @if($menuItem->ingredients->contains($product->id)) checked @endif
                                @endisset
                                class="size-4 rounded border-slate-300 text-slate-900 focus:ring-slate-300 ingredient-checkbox">
                            <label for="ingredient-{{ $product->id }}-{{ $prefix }}" class="flex-1 text-sm text-slate-700 cursor-pointer">
                                {{ $product->name }}
                                <span class="text-xs text-slate-500">({{ $product->unit }})</span>
                            </label>
                            <input type="number" step="0.01" min="0" name="ingredients[{{ $ingredientIndex }}][quantity]"
                                placeholder="Qty"
                                @isset($menuItem)
                                    @if($menuItem->ingredients->contains($product->id))
                                        value="{{ $existingIngredients[$product->id] ?? 0 }}"
                                    @else
                                        value="0"
                                    @endif
                                @else
                                    value="0"
                                @endisset
                                class="staff-input w-24 ingredient-quantity"
                                @isset($menuItem)
                                    @if(!$menuItem->ingredients->contains($product->id)) disabled @endif
                                @else
                                    disabled
                                @endisset>
                            @php $ingredientIndex++; @endphp
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @else
        <p class="text-sm text-slate-500">No ingredients available. Please add products to inventory first.</p>
        <p class="text-xs text-slate-400 mt-1">Debug: Products count: {{ isset($products) ? $products->count() : 'not set' }}</p>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.ingredient-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const quantityInput = this.closest('.flex').querySelector('.ingredient-quantity');
            quantityInput.disabled = !this.checked;
            if (!this.checked) {
                quantityInput.value = 0;
            }
        });
    });
});
</script>
