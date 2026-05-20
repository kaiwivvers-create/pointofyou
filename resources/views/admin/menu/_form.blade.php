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
