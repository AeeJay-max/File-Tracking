@extends('layouts.app')
@section('title', 'System Backup')

@section('breadcrumb')
<li class="breadcrumb-item active">Backup</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">System Backup</h1>
        <div class="page-subtitle">Create, download and manage database backups</div>
    </div>
    <form method="POST" action="{{ route('admin.backup.create') }}">
        @csrf
        <button type="submit" class="btn-portal-primary"
            onclick="return confirm('Create a new database backup now?')">
            <i class="fa-solid fa-database me-1"></i>Create Backup Now
        </button>
    </form>
</div>

{{-- Info card --}}
<div class="alert alert-info d-flex gap-2 align-items-start mb-4">
    <i class="fa-solid fa-circle-info fa-lg mt-1"></i>
    <div>
        <strong>Backup includes:</strong> Full MySQL database dump (all tables and data).
        Backups are stored securely in <code>storage/app/backups/</code> and are not publicly accessible.
        Only Super Admin can create, download or delete backups.
    </div>
</div>

{{-- Backup table --}}
<div class="portal-table-wrap">
    <div class="table-toolbar d-flex justify-content-between align-items-center">
        <span class="fw-700" style="font-size:.9rem;">
            <i class="fa-solid fa-archive me-2 text-primary"></i>
            {{ count($backups) }} backup{{ count($backups) !== 1 ? 's' : '' }} found
        </span>
    </div>
    <div class="table-responsive">
        <table class="portal-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Filename</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($backups as $i => $backup)
            <tr>
                <td class="text-muted">{{ $i + 1 }}</td>
                <td>
                    <div class="fw-700 fs-sm">
                        <i class="fa-solid fa-file-code me-1 text-muted"></i>
                        {{ $backup['filename'] }}
                    </div>
                </td>
                <td>
                    <span class="badge-status {{ $backup['type'] === 'Database' ? 'badge-transferred' : 'badge-active' }}">
                        {{ $backup['type'] }}
                    </span>
                </td>
                <td class="text-muted fs-sm">{{ $backup['size'] }}</td>
                <td class="text-muted fs-sm">{{ $backup['created_at']->format('d M Y, h:i A') }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ $backup['download_url'] }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-download"></i>
                        </a>
                        <form method="POST" action="{{ $backup['delete_url'] }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Delete this backup? This cannot be undone.')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">
                    <div class="empty-state">
                        <i class="fa-solid fa-database"></i>
                        No backups found. Create your first backup using the button above.
                    </div>
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Auto backup info --}}
<div class="portal-card mt-4">
    <div class="card-header"><i class="fa-solid fa-clock me-2 text-primary"></i>Scheduled Auto Backup</div>
    <div class="card-body">
        <p class="text-muted mb-2">Auto backup runs daily at <strong>02:00 AM</strong> via Laravel Scheduler.</p>
        <p class="text-muted small mb-0">
            Ensure the scheduler is running on your server:
            <code>* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1</code>
        </p>
    </div>
</div>
@endsection
