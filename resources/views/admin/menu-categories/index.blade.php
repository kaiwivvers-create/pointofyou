@extends('layouts.staff')

@section('title', 'Menu Categories')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Menu Categories</h1>
            <p class="mt-1 text-sm text-slate-500">Manage the order and visibility of categories on the kiosk and table menus.</p>
        </div>
    </div>

    <x-flash />

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm mb-8">
        <div class="bg-slate-50/80 px-4 py-3 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider flex justify-between">
            <span>Category Order</span>
            <span>Visibility</span>
        </div>
        
        <ul id="category-list" class="divide-y divide-slate-100">
            @foreach($categories as $category)
                <li class="flex items-center justify-between p-4 bg-white hover:bg-slate-50 transition-colors" data-id="{{ $category->id }}">
                    <div class="flex items-center gap-4">
                        <div class="cursor-grab active:cursor-grabbing text-slate-400 hover:text-slate-600 p-1 drag-handle">
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-900">{{ $category->label }}</p>
                            <p class="text-xs text-slate-500 font-mono">matches menu_items.category: "{{ $category->name }}"</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <button onclick="openEditModal({{ $category->id }}, '{{ $category->name }}', '{{ $category->label }}')" class="text-slate-400 hover:text-blue-600 p-2 rounded-lg hover:bg-blue-50 transition-colors" title="Edit Category">
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <form action="{{ route('admin.menu-categories.toggle', $category) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] focus:ring-offset-2 {{ $category->is_visible ? 'bg-[var(--primary-color)]' : 'bg-slate-200' }}" role="switch" aria-checked="{{ $category->is_visible ? 'true' : 'false' }}">
                                <span class="sr-only">Toggle visibility</span>
                                <span aria-hidden="true" class="pointer-events-none flex h-5 w-5 items-center justify-center transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $category->is_visible ? 'translate-x-5' : 'translate-x-0' }}">
                                    @if($category->is_visible)
                                        <svg class="h-3 w-3 text-[var(--primary-color)]" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    @else
                                        <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                        </svg>
                                    @endif
                                </span>
                            </button>
                        </form>

                        <form action="{{ route('admin.menu-categories.destroy', $category) }}" method="POST" class="inline-block" onsubmit="return confirm('Remove this category? Menu items using this category will remain, but won\'t show on menus until reassigned.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition-colors" title="Delete Category">
                                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
        @if($categories->isEmpty())
            <div class="p-8 text-center text-slate-500">
                No categories defined.
            </div>
        @endif
    </div>

    <!-- Add Category Form -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/80">
            <h2 class="text-lg font-semibold text-slate-900">Add New Category</h2>
            <p class="text-sm text-slate-500">The tag must match the "Category" field when adding menu items.</p>
        </div>
        <form action="{{ route('admin.menu-categories.store') }}" method="POST" class="p-6">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Display Label</label>
                    <input type="text" name="label" required maxlength="255" placeholder="e.g. Hot Drinks" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-[var(--primary-color)] focus:ring-[var(--primary-color)]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">System Tag (Lowercase, no spaces)</label>
                    <input type="text" name="name" required maxlength="100" placeholder="e.g. hot_drinks" pattern="^[a-z0-9_]+$" title="Only lowercase letters, numbers, and underscores" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-[var(--primary-color)] focus:ring-[var(--primary-color)] font-mono text-sm">
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <button type="submit" class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2" style="background-color: var(--primary-color); hover:brightness-90;">
                    <svg class="size-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Category
                </button>
            </div>
        </form>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all duration-200 scale-95 opacity-0" id="editModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Edit Category</h2>
            </div>
            <form method="POST" action="" id="editForm" class="p-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="editId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Display Label</label>
                    <input type="text" name="label" id="editLabel" required maxlength="255" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-[var(--primary-color)] focus:ring-[var(--primary-color)]">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">System Tag (Lowercase, no spaces)</label>
                    <input type="text" name="name" id="editName" required maxlength="100" pattern="^[a-z0-9_]+$" title="Only lowercase letters, numbers, and underscores" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-[var(--primary-color)] focus:ring-[var(--primary-color)] font-mono text-sm">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeEditModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    function openEditModal(id, name, label) {
        const modal = document.getElementById('editModal');
        const content = document.getElementById('editModalContent');
        const form = document.getElementById('editForm');
        
        document.getElementById('editId').value = id;
        document.getElementById('editName').value = name;
        document.getElementById('editLabel').value = label;
        form.action = '{{ route('admin.menu-categories.update', ':id') }}'.replace(':id', id);
        
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
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 200);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('category-list');
        if (el) {
            Sortable.create(el, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'bg-slate-100',
                onEnd: function () {
                    const items = Array.from(el.children).map((li, index) => {
                        return {
                            id: li.dataset.id,
                            sort_order: index + 1
                        };
                    });

                    fetch('{{ route('admin.menu-categories.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ order: items })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            console.error('Failed to reorder');
                        }
                    })
                    .catch(err => console.error(err));
                }
            });
        }
    });
</script>
@endpush
