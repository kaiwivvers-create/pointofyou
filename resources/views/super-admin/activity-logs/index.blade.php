@extends('layouts.staff')

@section('title', 'Activity Logs')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Activity Logs</h1>
            <p class="staff-page-subtitle">Track all system changes with IP, time, and metadata.</p>
        </div>
    </div>

    <x-flash />

    <!-- Search and Filter -->
    <form method="GET" action="{{ route('super-admin.activity-logs.index') }}" class="staff-card p-4 mb-6 flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <input type="text" name="model_type" value="{{ request('model_type') }}" placeholder="Filter by model type..." class="staff-input">
        </div>
        <div class="sm:w-40">
            <select name="action" class="staff-input">
                <option value="">All Actions</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:w-40">
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="staff-input" placeholder="From">
        </div>
        <div class="sm:w-40">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="staff-input" placeholder="To">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="staff-btn-primary">Filter</button>
            @if (request('action') || request('model_type') || request('date_from') || request('date_to'))
                <a href="{{ route('super-admin.activity-logs.index') }}" class="staff-btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="staff-table-wrap">
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>IP Address</th>
                        <th>Action</th>
                        <th>Model</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td class="text-sm">{{ $log->occurred_at->format('M j, Y g:i A') }}</td>
                            <td>{{ $log->user ? $log->user->name : 'System' }}</td>
                            <td class="text-sm text-slate-500">{{ $log->ip_address ?? '-' }}</td>
                            <td>
                                <span class="staff-badge-{{ $log->action === 'deleted' ? 'red' : ($log->action === 'created' ? 'green' : 'amber') }}">
                                    {{ ucfirst($log->action) }}
                                </span>
                            </td>
                            <td class="text-sm">
                                {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                            </td>
                            <td class="text-right space-x-4">
                                <button onclick="viewLog({{ $log->id }})" class="staff-link">View</button>
                                @if (in_array($log->action, ['deleted', 'updated']))
                                    <form method="POST" action="{{ route('super-admin.activity-logs.revert', $log) }}" class="inline" onsubmit="return confirm('Revert this {{ $log->action }} action?')">
                                        @csrf
                                        <button type="submit" class="staff-link">Revert</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('super-admin.activity-logs.destroy', $log) }}" class="inline" onsubmit="return confirm('Delete this log entry?')">
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

    @if ($logs->hasPages())
        <div class="mt-6 flex justify-center">
            {{ $logs->appends(request()->only(['action', 'model_type', 'date_from', 'date_to']))->links() }}
        </div>
    @endif

    <!-- Activity Log Modal -->
    <div id="activity-log-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto transform scale-95 transition-transform duration-300" id="activity-log-modal-content">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-slate-900">Activity Log Details</h2>
                    <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div id="activity-log-content">
                    <div class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-slate-900"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewLog(id) {
            const modal = document.getElementById('activity-log-modal');
            const modalContent = document.getElementById('activity-log-modal-content');
            const content = document.getElementById('activity-log-content');
            
            // Show modal with animation
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
            }, 10);
            
            // Load content
            content.innerHTML = '<div class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-slate-900"></div></div>';
            
            fetch(`{{ route('super-admin.activity-logs.show', ':id') }}`.replace(':id', id))
                .then(response => response.text())
                .then(html => {
                    content.innerHTML = html;
                })
                .catch(error => {
                    content.innerHTML = '<div class="text-center py-8 text-red-600">Failed to load log details.</div>';
                });
        }
        
        function closeModal() {
            const modal = document.getElementById('activity-log-modal');
            const modalContent = document.getElementById('activity-log-modal-content');
            
            // Animate out
            modal.classList.add('opacity-0');
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }
        
        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
        
        // Close modal on backdrop click
        document.getElementById('activity-log-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
@endsection
