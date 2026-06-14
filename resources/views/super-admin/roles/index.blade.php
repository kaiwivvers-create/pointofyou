@extends('layouts.staff')

@section('title', 'Roles & Wages')

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
            <h1 class="staff-page-title">Roles & Wages</h1>
            <p class="staff-page-subtitle">Manage roles and their salary/wage settings.</p>
        </div>
        <button onclick="openCreateModal()" class="staff-btn-primary">Add Role</button>
    </div>

    <x-flash />

    <div class="staff-table-wrap">
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Role Name</th>
                        <th>Description</th>
                        <th>Paid</th>
                        <th>Base Salary</th>
                        <th>Frequency</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <td class="font-semibold text-slate-900">{{ $role->name }}</td>
                            <td class="text-slate-600 max-w-xs truncate" title="{{ $role->description ?? '-' }}">{{ $role->description ?? '-' }}</td>
                            <td>
                                @if ($role->is_paid)
                                    <span class="staff-badge-green">Yes</span>
                                @else
                                    <span class="staff-badge-muted">No</span>
                                @endif
                            </td>
                            <td>{{ $role->is_paid ? '$' . number_format($role->base_salary, 2) : '-' }}</td>
                            <td>{{ $role->is_paid ? ucfirst($role->payment_frequency) : '-' }}</td>
                            <td class="text-right space-x-4">
                                <button onclick="openEditModal({{ $role->toJson() }})" class="staff-link">Edit</button>
                                <form method="POST" action="{{ route('super-admin.roles.destroy', $role) }}" class="inline" onsubmit="return confirm('Delete {{ $role->name }}? Users with this role will be unassigned.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="staff-link-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div id="createModalContent" class="bg-white rounded-2xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden transform transition-all duration-200 scale-95 opacity-0">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Add Role</h2>
                <p class="text-sm text-slate-500 mt-1">Create a new role with wage settings.</p>
            </div>
            <form method="POST" action="{{ route('super-admin.roles.store') }}" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Role Name</label>
                        <input type="text" name="name" required maxlength="255" class="staff-input" placeholder="e.g., Assistant Manager">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Slug</label>
                        <input type="text" name="slug" required maxlength="255" class="staff-input" placeholder="e.g., assistant_manager">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                        <textarea name="description" rows="2" maxlength="5000" class="staff-input" placeholder="Role description..."></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_paid" value="0">
                        <input type="checkbox" name="is_paid" id="is_paid" value="1" class="size-5 rounded border-slate-300 text-slate-900 focus:ring-slate-300" onchange="toggleWageFields()">
                        <label for="is_paid" class="text-sm font-medium text-slate-700">This role receives salary/wage</label>
                    </div>
                    <div id="wageFields" class="hidden space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Base Salary</label>
                            <input type="number" name="base_salary" step="0.01" min="0" class="staff-input" placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Payment Frequency</label>
                            <select name="payment_frequency" class="staff-input">
                                <option value="monthly">Monthly</option>
                                <option value="bi-weekly">Bi-Weekly</option>
                                <option value="weekly">Weekly</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeCreateModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Create Role</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div id="editModalContent" class="bg-white rounded-2xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden transform transition-all duration-200 scale-95 opacity-0">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Edit Role</h2>
            </div>
            <form id="editForm" method="POST" class="p-6">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Role Name</label>
                        <input type="text" name="name" required maxlength="255" class="staff-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Slug</label>
                        <input type="text" name="slug" required maxlength="255" class="staff-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                        <textarea name="description" rows="2" maxlength="5000" class="staff-input"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_paid" value="0">
                        <input type="checkbox" name="is_paid" id="edit_is_paid" value="1" class="size-5 rounded border-slate-300 text-slate-900 focus:ring-slate-300" onchange="toggleEditWageFields()">
                        <label for="edit_is_paid" class="text-sm font-medium text-slate-700">This role receives salary/wage</label>
                    </div>
                    <div id="editWageFields" class="hidden space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Base Salary</label>
                            <input type="number" name="base_salary" step="0.01" min="0" class="staff-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Payment Frequency</label>
                            <select name="payment_frequency" class="staff-input">
                                <option value="monthly">Monthly</option>
                                <option value="bi-weekly">Bi-Weekly</option>
                                <option value="weekly">Weekly</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeEditModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleWageFields() {
            const isPaid = document.getElementById('is_paid').checked;
            const wageFields = document.getElementById('wageFields');
            wageFields.classList.toggle('hidden', !isPaid);
        }

        function toggleEditWageFields() {
            const isPaid = document.getElementById('edit_is_paid').checked;
            const wageFields = document.getElementById('editWageFields');
            wageFields.classList.toggle('hidden', !isPaid);
        }

        function openCreateModal() {
            const modal = document.getElementById('createModal');
            const content = document.getElementById('createModalContent');
            const form = modal.querySelector('form');
            
            form.reset();
            document.getElementById('wageFields').classList.add('hidden');
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeCreateModal() {
            const modal = document.getElementById('createModal');
            const content = document.getElementById('createModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        function openEditModal(role) {
            const modal = document.getElementById('editModal');
            const content = document.getElementById('editModalContent');
            const form = document.getElementById('editForm');
            form.action = '{{ url("/super-admin/roles") }}/' + role.id;
            
            form.querySelector('[name="name"]').value = role.name;
            form.querySelector('[name="slug"]').value = role.slug;
            form.querySelector('[name="description"]').value = role.description || '';
            document.getElementById('edit_is_paid').checked = role.is_paid;
            form.querySelector('[name="base_salary"]').value = role.base_salary || '';
            form.querySelector('[name="payment_frequency"]').value = role.payment_frequency || 'monthly';
            toggleEditWageFields();

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

        document.getElementById('createModal').addEventListener('click', function(e) {
            if (e.target === this) closeCreateModal();
        });

        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });
    </script>
@endpush
