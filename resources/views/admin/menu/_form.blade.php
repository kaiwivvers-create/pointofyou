@php
    $menuItem = $menuItem ?? null;
@endphp

<div class="space-y-5">
    <div>
        <label for="name" class="staff-label">Name</label>
        <input id="name" name="name" type="text" required value="{{ old('name', $menuItem?->name) }}" class="staff-input max-w-md">
    </div>
    <div>
        <label for="description" class="staff-label">Description</label>
        <textarea id="description" name="description" rows="2" class="staff-input max-w-md">{{ old('description', $menuItem?->description) }}</textarea>
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
        <div>
            <label for="emoji" class="staff-label">Emoji</label>
            <input id="emoji" name="emoji" type="text" maxlength="10" value="{{ old('emoji', $menuItem?->emoji) }}" class="staff-input" placeholder="🥐">
        </div>
    </div>
    <label class="flex items-center gap-2.5 cursor-pointer select-none">
        <input type="hidden" name="is_available" value="0">
        <input type="checkbox" name="is_available" value="1" class="size-4 rounded border-amber-300 text-amber-800 focus:ring-amber-300"
            @checked(old('is_available', $menuItem?->is_available ?? true))>
        <span class="text-sm text-stone-600">Available on menu</span>
    </label>
</div>

<div class="border-t border-stone-200 pt-5 mt-5">
    <h3 class="text-sm font-medium text-stone-800 mb-2">Customizations / Add-ons</h3>
    <p class="text-xs text-stone-500 mb-4">Add options like "No Pickles" (price: 0) or "Extra Cheese" (price: 1.50).</p>
    
    <div id="modifications-container" class="space-y-3">
        @if(old('modifications'))
            @foreach(old('modifications') as $index => $mod)
                <div class="flex items-center gap-2 modification-row">
                    <input type="text" name="modifications[{{ $index }}][name]" value="{{ $mod['name'] }}" placeholder="Name (e.g. No Mayo)" required class="staff-input flex-1">
                    <input type="number" step="0.01" min="0" name="modifications[{{ $index }}][additional_price]" value="{{ $mod['additional_price'] }}" placeholder="+ Price ($)" required class="staff-input w-28">
                    <button type="button" class="text-red-500 hover:text-red-700 text-sm px-2 py-1 remove-mod text-xl font-bold">&times;</button>
                </div>
            @endforeach
        @elseif($menuItem?->modifications)
            @foreach($menuItem->modifications as $index => $mod)
                <div class="flex items-center gap-2 modification-row">
                    <input type="text" name="modifications[{{ $index }}][name]" value="{{ $mod->name }}" placeholder="Name (e.g. No Mayo)" required class="staff-input flex-1">
                    <input type="number" step="0.01" min="0" name="modifications[{{ $index }}][additional_price]" value="{{ $mod->additional_price }}" placeholder="+ Price ($)" required class="staff-input w-28">
                    <button type="button" class="text-red-500 hover:text-red-700 text-sm px-2 py-1 remove-mod text-xl font-bold">&times;</button>
                </div>
            @endforeach
        @endif
    </div>
    
    <button type="button" id="add-mod-btn" class="mt-3 text-sm text-amber-700 hover:text-amber-800 font-medium">+ Add customization</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('modifications-container');
    const addBtn = document.getElementById('add-mod-btn');
    
    let modIndex = container.querySelectorAll('.modification-row').length;
    
    addBtn.addEventListener('click', function() {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 modification-row';
        div.innerHTML = `
            <input type="text" name="modifications[${modIndex}][name]" placeholder="Name (e.g. No Mayo)" required class="staff-input flex-1">
            <input type="number" step="0.01" min="0" name="modifications[${modIndex}][additional_price]" value="0" placeholder="+ Price ($)" required class="staff-input w-28">
            <button type="button" class="text-red-500 hover:text-red-700 text-sm px-2 py-1 remove-mod text-xl font-bold">&times;</button>
        `;
        container.appendChild(div);
        modIndex++;
    });
    
    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-mod')) {
            e.target.closest('.modification-row').remove();
        }
    });
});
</script>
