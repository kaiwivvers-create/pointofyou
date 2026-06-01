<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Basic Info -->
    <div class="staff-card p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Basic Information</h2>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-slate-500">ID:</span>
                <span class="font-medium">{{ $activityLog->id }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Time:</span>
                <span class="font-medium">{{ $activityLog->occurred_at->format('M j, Y g:i A') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">User:</span>
                <span class="font-medium">{{ $activityLog->user ? $activityLog->user->name : 'System' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">IP Address:</span>
                <span class="font-medium">{{ $activityLog->ip_address ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Action:</span>
                <span class="staff-badge-{{ $activityLog->action === 'deleted' ? 'red' : ($activityLog->action === 'created' ? 'green' : 'amber') }}">
                    {{ ucfirst($activityLog->action) }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Model Type:</span>
                <span class="font-medium">{{ $activityLog->model_type }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Model ID:</span>
                <span class="font-medium">{{ $activityLog->model_id ?? '-' }}</span>
            </div>
        </div>
    </div>

    <!-- Metadata -->
    <div class="staff-card p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Metadata</h2>
        @if ($activityLog->metadata)
            <pre class="bg-slate-50 p-4 rounded-lg text-sm overflow-auto max-h-64">{{ json_encode($activityLog->metadata, JSON_PRETTY_PRINT) }}</pre>
        @else
            <p class="text-slate-500">No metadata available.</p>
        @endif
    </div>

    <!-- Old Values -->
    @if ($activityLog->old_values)
        <div class="staff-card p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Old Values</h2>
            <pre class="bg-slate-50 p-4 rounded-lg text-sm overflow-auto max-h-64">{{ json_encode($activityLog->old_values, JSON_PRETTY_PRINT) }}</pre>
        </div>
    @endif

    <!-- New Values -->
    @if ($activityLog->new_values)
        <div class="staff-card p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">New Values</h2>
            <pre class="bg-slate-50 p-4 rounded-lg text-sm overflow-auto max-h-64">{{ json_encode($activityLog->new_values, JSON_PRETTY_PRINT) }}</pre>
        </div>
    @endif
</div>

<!-- Actions -->
<div class="mt-6 flex flex-wrap gap-3">
    @if (in_array($activityLog->action, ['deleted', 'updated']))
        <form method="POST" action="{{ route('super-admin.activity-logs.revert', $activityLog) }}" onsubmit="return confirm('Revert this {{ $activityLog->action }} action?'); closeModal();">
            @csrf
            <button type="submit" class="staff-btn-primary">Revert Action</button>
        </form>
    @endif
    <form method="POST" action="{{ route('super-admin.activity-logs.destroy', $activityLog) }}" onsubmit="return confirm('Delete this log entry?'); closeModal();">
        @csrf
        @method('DELETE')
        <button type="submit" class="staff-btn-secondary">Delete Log</button>
    </form>
    <form method="POST" action="{{ route('super-admin.activity-logs.destroy-permanently', $activityLog) }}" onsubmit="return confirm('Permanently delete this log entry? This cannot be undone.'); closeModal();">
        @csrf
        @method('DELETE')
        <button type="submit" class="staff-btn-danger">Permanently Delete</button>
    </form>
</div>
