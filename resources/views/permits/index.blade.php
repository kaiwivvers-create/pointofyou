@extends('layouts.staff')

@section('title', 'Permits')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Permits</h1>
            <p class="staff-page-subtitle">Manage leave requests, overtime requests, and other permits.</p>
        </div>
        <button onclick="openCreateModal()" class="staff-btn-primary">Request Permit</button>
    </div>

    <x-flash />

    <div class="staff-table-wrap">
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Approved By</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($permits as $permit)
                        <tr>
                            <td class="font-semibold text-slate-900">{{ $permit->user ? $permit->user->name : '-' }}</td>
                            <td class="text-slate-600">{{ ucfirst($permit->type) }}</td>
                            <td class="text-slate-900">{{ $permit->start_date->format('M d, Y') }}</td>
                            <td class="text-slate-900">{{ $permit->end_date ? $permit->end_date->format('M d, Y') : '-' }}</td>
                            <td class="text-slate-600 max-w-xs truncate">{{ $permit->reason }}</td>
                            <td>
                                @if ($permit->status === 'pending')
                                    <span class="staff-badge-yellow">Pending</span>
                                @elseif ($permit->status === 'approved')
                                    <span class="staff-badge-green">Approved</span>
                                @else
                                    <span class="staff-badge-red">Rejected</span>
                                @endif
                            </td>
                            <td class="text-slate-600">{{ $permit->approvedBy ? $permit->approvedBy->name : '-' }}</td>
                            <td class="text-right space-x-2">
                                @if ($permit->status === 'pending')
                                    <form method="POST" action="{{ route('permits.approve', $permit) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="staff-link text-emerald-600">Approve</button>
                                    </form>
                                    <button onclick="openRejectModal({{ $permit->id }})" class="staff-link text-red-600">Reject</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-16 text-center text-slate-500">No permit requests yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($permits->hasPages())
        <div class="mt-6 flex justify-center">
            {{ $permits->links() }}
        </div>
    @endif

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 transform transition-all duration-200 scale-95 opacity-0" id="createModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Request Permit</h2>
            </div>
            <form method="POST" action="{{ route('permits.store') }}" class="p-6">
                @csrf
                <div class="mb-4">
                    <label for="type" class="staff-label">Permit Type</label>
                    <select id="type" name="type" required class="staff-input">
                        <option value="">Select type</option>
                        <option value="leave">Leave</option>
                        <option value="overtime">Overtime</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="start_date" class="staff-label">Start Date</label>
                    <input type="date" id="start_date" name="start_date" required class="staff-input">
                </div>

                <div class="mb-4">
                    <label for="end_date" class="staff-label">End Date (Optional)</label>
                    <input type="date" id="end_date" name="end_date" class="staff-input">
                    <p class="text-xs text-slate-500 mt-1">Leave blank for single-day permits</p>
                </div>

                <div class="mb-4">
                    <label for="reason" class="staff-label">Reason</label>
                    <textarea id="reason" name="reason" rows="4" required maxlength="5000" class="staff-input" placeholder="Explain why you need this permit..."></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeCreateModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all duration-200 scale-95 opacity-0" id="rejectModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Reject Permit</h2>
            </div>
            <form method="POST" action="" id="rejectForm" class="p-6">
                @csrf
                <input type="hidden" name="_method" value="PATCH">
                <div class="mb-4">
                    <label for="rejection_reason" class="staff-label">Rejection Reason</label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="3" required maxlength="5000" class="staff-input"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeRejectModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary bg-red-600 hover:bg-red-700">Reject</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            const modal = document.getElementById('createModal');
            const content = document.getElementById('createModalContent');
            
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
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 200);
        }

        function openRejectModal(permitId) {
            const modal = document.getElementById('rejectModal');
            const content = document.getElementById('rejectModalContent');
            const form = document.getElementById('rejectForm');
            
            form.action = '{{ route('permits.reject', ':id') }}'.replace(':id', permitId);
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeRejectModal() {
            const modal = document.getElementById('rejectModal');
            const content = document.getElementById('rejectModalContent');
            
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 200);
        }
    </script>
@endsection
