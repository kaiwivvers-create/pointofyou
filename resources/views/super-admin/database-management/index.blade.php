@extends('layouts.staff')

@section('title', 'Database Management')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Database Management</h1>
            <p class="staff-page-subtitle">Export, import, and manage your database backups.</p>
        </div>
    </div>

    <x-flash />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Backup Operations -->
        <div class="staff-card p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Backup Operations</h2>
            
            <form method="POST" action="{{ route('super-admin.database.export') }}" class="mb-4">
                @csrf
                <div class="flex gap-2 mb-3">
                    <select name="format" class="staff-input flex-1">
                        <option value="sqlite">SQLite (.sqlite)</option>
                        <option value="sql">SQL (.sql)</option>
                        <option value="json">JSON (.json)</option>
                    </select>
                </div>
                <button type="submit" class="staff-btn-primary w-full">
                    Export Database
                </button>
            </form>

            <form method="POST" action="{{ route('super-admin.database.import') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="staff-label">Import Database Backup</label>
                    <input type="file" name="backup_file" accept=".sqlite,.sql" class="staff-input" required>
                    <p class="text-xs text-slate-500 mt-1">Warning: This will replace the current database.</p>
                </div>
                <button type="submit" class="staff-btn-secondary w-full" onclick="return confirm('This will replace the current database. Are you sure?')">
                    Import Database
                </button>
            </form>
        </div>

        <!-- System Operations -->
        <div class="staff-card p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">System Operations</h2>
            
            <div class="space-y-3">
                <form method="POST" action="{{ route('super-admin.database.clear-cache') }}">
                    @csrf
                    <button type="submit" class="staff-btn-secondary w-full" onclick="return confirm('Clear all caches?')">
                        Clear All Caches
                    </button>
                </form>

                <form method="POST" action="{{ route('super-admin.database.optimize') }}">
                    @csrf
                    <button type="submit" class="staff-btn-secondary w-full" onclick="return confirm('Optimize application?')">
                        Optimize Application
                    </button>
                </form>

                <form method="POST" action="{{ route('super-admin.database.migrate') }}">
                    @csrf
                    <button type="submit" class="staff-btn-secondary w-full" onclick="return confirm('Run database migrations?')">
                        Run Migrations
                    </button>
                </form>

                <form method="POST" action="{{ route('super-admin.database.seed') }}">
                    @csrf
                    <div class="flex gap-2">
                        <input type="text" name="seeder" maxlength="255" placeholder="Seeder class (optional)" class="staff-input flex-1">
                        <button type="submit" class="staff-btn-secondary" onclick="return confirm('Seed database?')">
                            Seed
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Backup Files -->
    <div class="staff-card p-6 mt-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Backup Files</h2>
        
        @if (empty($backupFiles))
            <p class="text-slate-500">No backup files found.</p>
        @else
            <div class="overflow-x-auto">
                <table class="staff-table">
                    <thead>
                        <tr>
                            <th>Filename</th>
                            <th>Size</th>
                            <th>Date</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($backupFiles as $file)
                            <tr>
                                <td class="font-medium">{{ $file['name'] }}</td>
                                <td>{{ number_format($file['size'] / 1024 / 1024, 2) }} MB</td>
                                <td>{{ date('M j, Y g:i A', $file['modified']) }}</td>
                                <td class="text-right space-x-4">
                                    <a href="{{ route('super-admin.database.download', $file['name']) }}" class="staff-link">Download</a>
                                    <form method="POST" action="{{ route('super-admin.database.delete', $file['name']) }}" class="inline" onsubmit="return confirm('Delete this backup?')">
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
        @endif
    </div>
@endsection
