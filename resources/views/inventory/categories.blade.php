@extends('layouts.staff')

@section('title', 'Inventory Categories')

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
            <h1 class="staff-page-title">Inventory Categories</h1>
            <p class="staff-page-subtitle">Organize products into categories.</p>
        </div>
        @if ($can('inventory', 'edit'))
            <button onclick="openAddCategoryModal()" class="staff-btn-primary">Add Category</button>
        @endif
    </div>

    <x-flash />

    <div class="staff-tabs mb-6">
        <button onclick="window.location.href='{{ route('inventory.index') }}'" class="staff-tab {{ request()->routeIs('inventory.index') ? 'staff-tab-active' : '' }}">Products</button>
        <button onclick="window.location.href='{{ route('inventory.categories') }}'" class="staff-tab {{ request()->routeIs('inventory.categories') ? 'staff-tab-active' : '' }}">Categories</button>
        <button onclick="window.location.href='{{ route('inventory.stock-movements') }}'" class="staff-tab {{ request()->routeIs('inventory.stock-movements') ? 'staff-tab-active' : '' }}">Stock Movements</button>
    </div>

    <div class="staff-table-wrap">
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="font-semibold text-slate-900">{{ $category->name }}</td>
                            <td class="text-slate-600">{{ $category->description ?? '-' }}</td>
                            <td class="text-right space-x-4">
                                <span class="text-slate-400 text-sm">{{ $category->products()->count() }} products</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-16 text-center text-slate-500">No categories yet. Add your first category!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0" id="addCategoryModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Add Category</h2>
                <p class="text-sm text-slate-500 mt-1">Create a new product category.</p>
            </div>
            <form method="POST" action="{{ route('inventory.categories.store') }}" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                        <input type="text" name="name" required class="staff-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                        <textarea name="description" rows="3" class="staff-input"></textarea>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeAddCategoryModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddCategoryModal() {
            const modal = document.getElementById('addCategoryModal');
            const content = document.getElementById('addCategoryModalContent');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeAddCategoryModal() {
            const modal = document.getElementById('addCategoryModal');
            const content = document.getElementById('addCategoryModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        document.getElementById('addCategoryModal').addEventListener('click', function(e) {
            if (e.target === this) closeAddCategoryModal();
        });
    </script>
@endsection
