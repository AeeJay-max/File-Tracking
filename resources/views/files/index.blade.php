@extends('layouts.app')
@section('title', 'Files')

@section('breadcrumb')
<li class="breadcrumb-item active">Files</li>
@endsection

@section('content')

{{-- ── PAGE HEADER ─────────────────────────────────────────── --}}
<div class="page-header">
    <div>
        <h1 class="page-title">
            @if($selectedFolder)
                Folder: {{ $selectedFolder->folder_number }} &mdash; {{ $selectedFolder->folder_name }}
            @elseif($isRecordsDept)
                Records Department File Registry
            @elseif(auth()->user()->role === 'user')
                My Files
            @else
                Department Files
            @endif
        </h1>
        <div class="page-subtitle">
            @if($selectedFolder)
                Showing files contained inside folder {{ $selectedFolder->folder_number }}
            @elseif($showFoldersView)
                Select a folder to view files contained within it
            @else
                Manage and track official documents
            @endif
        </div>
    </div>
    <div class="d-flex gap-2">
        @if($selectedFolder || (request()->filled('search') && $isRecordsDept))
        <a href="{{ route('files.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>All Folders
        </a>
        @endif

        @can('create', App\Models\FileRecord::class)
        <button type="button" class="btn-portal-primary" data-bs-toggle="modal" data-bs-target="#createFileModal">
            <i class="fa-solid fa-plus me-1"></i>New File
        </button>
        @endcan
    </div>
</div>

@if($showFoldersView)
{{-- ═══════════════════════════════════════════════════════════
     RECORDS DEPARTMENT: FOLDERS VIEW (Default for Records Dept)
═══════════════════════════════════════════════════════════ --}}
<div class="portal-table-wrap mb-4">
    <div class="p-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2" style="background:#f8fafc;">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-folder-tree fa-lg text-primary"></i>
            <strong class="text-dark fs-5">Folders Registry</strong>
            <span class="text-muted fs-sm">(Click a folder to view contained files)</span>
        </div>
        <a href="{{ route('folders.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="fa-solid fa-folder-plus me-1"></i>Manage Folders
        </a>
    </div>

    <div class="row g-3 p-3">
        @forelse($folders as $folder)
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('files.index', ['folder_id' => $folder->id]) }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm border-0 portal-folder-card" style="border-radius:12px;background:#ffffff;transition:transform .15s ease, shadow .15s ease;border:1px solid #e2e8f0;">
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-700 px-2.5 py-1" style="border-radius:6px;font-size:.8rem;">
                                    <i class="fa-solid fa-folder me-1"></i>{{ $folder->folder_number }}
                                </span>
                                <span class="badge bg-secondary bg-opacity-10 text-dark fw-600 fs-xs" style="border-radius:12px;">
                                    <i class="fa-solid fa-file me-1 text-primary"></i>{{ $folder->files_count }} {{ Str::plural('File', $folder->files_count) }}
                                </span>
                            </div>
                            <h5 class="fw-700 text-dark mb-1" style="font-size:1.05rem;">{{ $folder->folder_name }}</h5>
                            <p class="text-muted fs-xs mb-3">
                                <i class="fa-solid fa-building-columns me-1"></i>{{ $folder->department->name ?? 'Global Records' }}
                            </p>
                        </div>
                        <div class="pt-2 border-top d-flex align-items-center justify-content-between text-muted fs-xs">
                            <span><i class="fa-solid fa-clock me-1"></i>Updated {{ $folder->updated_at->diffForHumans() }}</span>
                            <span class="text-primary fw-600">Open Folder &rarr;</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12 py-5 text-center text-muted">
            <i class="fa-solid fa-folder-open fa-3x mb-2 text-secondary opacity-50"></i>
            <p class="mb-2 fw-600">No folders created yet.</p>
            <a href="{{ route('files.create') }}" class="btn btn-sm btn-primary">
                <i class="fa-solid fa-plus me-1"></i>Create File &amp; Folder
            </a>
        </div>
        @endforelse
    </div>

    @if($folders->hasPages())
    <div class="px-4 py-3 border-top">
        {{ $folders->withQueryString()->links() }}
    </div>
    @endif
</div>
@else

{{-- ═══════════════════════════════════════════════════════════
     FILES TABLE (Search, Selected Folder, or Non-Records User)
═══════════════════════════════════════════════════════════ --}}
<div class="portal-table-wrap mb-0">
    <form action="{{ route('files.index') }}" method="GET" class="table-toolbar">
        @if($selectedFolder)
        <input type="hidden" name="folder_id" value="{{ $selectedFolder->id }}">
        @endif

        <input type="text" name="search" class="form-control" style="max-width:220px;min-width:180px;"
            placeholder="Search name or number..."
            value="{{ request('search', '') }}">

        <select name="status" class="form-select" style="max-width:160px;min-width:140px;">
            <option value="">All Statuses</option>
            <option value="active"             {{ request('status') === 'active'             ? 'selected' : '' }}>Active</option>
            <option value="pending_assignment" {{ request('status') === 'pending_assignment' ? 'selected' : '' }}>Awaiting Assignment</option>
            <option value="archived"           {{ request('status') === 'archived'           ? 'selected' : '' }}>Archived</option>
        </select>

        <input type="date" name="from_date" class="form-control" style="max-width:145px;"
            value="{{ request('from_date', '') }}">
        <input type="date" name="to_date"   class="form-control" style="max-width:145px;"
            value="{{ request('to_date', '') }}">

        <button type="submit" class="btn btn-primary btn-sm px-3">
            <i class="fa-solid fa-magnifying-glass me-1"></i>Search
        </button>
        <a href="{{ route('files.index', $selectedFolder ? ['folder_id' => $selectedFolder->id] : []) }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
    </form>

    {{-- ── TABLE ──────────────────────────────────────────────── --}}
    <div class="table-responsive">
        <table class="portal-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>File &amp; Folder</th>
                    <th>File Name</th>
                    <th>Origin Dept</th>
                    <th>Current Holder / Dept</th>
                    @if($isRecordsDept)
                    <th>Assigned Admin &amp; Officer</th>
                    @endif
                    <th>Status &amp; Urgency</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($files as $i => $file)
            @php
                $isUrgent = (bool) $file->is_urgent;
                $hasDeadline = $file->return_deadline !== null;
                $isOverdue = $hasDeadline && now()->greaterThan($file->return_deadline);
            @endphp
            <tr class="{{ $isUrgent || $isOverdue ? 'table-danger border-start border-danger border-4' : '' }}">
                <td class="text-muted">{{ $files->firstItem() + $i }}</td>
                <td>
                    <span class="fw-700 text-portal-primary d-block">{{ $file->file_number }}</span>
                    @if($file->folder)
                    <small class="badge bg-secondary bg-opacity-10 text-dark">
                        <i class="fa-solid fa-folder me-1 text-primary"></i>{{ $file->folder->folder_number }}
                    </small>
                    @endif
                </td>
                <td>
                    <div class="fw-600">{{ $file->file_name }}</div>
                    @if($isUrgent)
                    <span class="badge bg-danger text-white mt-1">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>URGENT
                    </span>
                    @endif
                </td>
                <td class="text-muted">{{ $file->department->name ?? 'N/A' }}</td>

                {{-- CURRENT HOLDER (Includes Department Name) --}}
                <td>
                    <div class="d-flex flex-column align-items-start gap-1">
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-600 px-2 py-1" style="border-radius:6px;">
                            <i class="fa-solid fa-building-columns me-1"></i>{{ $file->currentDepartment->name ?? 'N/A' }}
                        </span>
                        @if($file->currentHolder)
                        <small class="text-dark fw-600 ms-1">
                            <i class="fa-solid fa-user me-1 text-secondary"></i>{{ $file->currentHolder->name }}
                        </small>
                        @elseif($file->status === 'pending_assignment')
                        <small class="text-warning fw-600 ms-1">
                            <i class="fa-solid fa-clock me-1"></i>Awaiting Admin Assignment
                        </small>
                        @endif
                    </div>
                </td>

                {{-- RECORDS DEPT EXTRA DETAILS --}}
                @if($isRecordsDept)
                @php
                    $lastAssignMovement = $file->movements()->whereIn('action', ['assigned', 'transferred'])->latest()->first();
                @endphp
                <td>
                    @if($lastAssignMovement)
                    <div class="fs-xs">
                        <span class="d-block text-muted">Admin: <strong class="text-dark">{{ $lastAssignMovement->fromUser->name ?? 'System' }}</strong></span>
                        <span class="d-block text-muted">Assigned: <strong class="text-dark">{{ $lastAssignMovement->toUser->name ?? ($file->currentDepartment->name ?? 'Department') }}</strong></span>
                    </div>
                    @else
                    <span class="text-muted fs-xs">—</span>
                    @endif
                </td>
                @endif

                <td>
                    @include('partials.status-badge', ['status' => $file->status])

                    @if($hasDeadline && $file->status !== 'completed' && $file->status !== 'archived')
                    <div class="mt-1">
                        @if($isOverdue)
                        <span class="badge bg-danger text-white">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i>Return Overdue!
                        </span>
                        @else
                        <span class="badge bg-warning text-dark">
                            <i class="fa-solid fa-hourglass-half me-1"></i>Return in {{ $file->return_deadline->diffForHumans(['parts' => 2]) }}
                        </span>
                        @endif
                    </div>
                    @endif
                </td>

                <td class="text-muted fs-sm">{{ $file->created_at->format('d M Y') }}</td>
                <td>
                    <div class="d-flex gap-1 align-items-center">
                        <a href="{{ route('files.show', $file->uuid) }}"
                           class="btn btn-sm btn-outline-primary" title="View">
                            <i class="fa-solid fa-eye"></i>
                        </a>

                        @if($file->status !== 'archived' && (int)$file->current_user_id === auth()->id())
                            <a href="{{ route('files.transfer.create', $file->uuid) }}"
                               class="btn btn-sm btn-outline-secondary" title="Transfer file">
                                <i class="fa-solid fa-right-left me-1"></i>Transfer
                            </a>
                        @elseif($file->status !== 'archived' && (int)$file->created_by === auth()->id() && (int)$file->current_user_id !== auth()->id())
                            <span class="badge-status badge-transferred" title="You previously transferred this file">
                                <i class="fa-solid fa-history me-1"></i>Transferred
                            </span>
                        @endif

                        <a href="{{ route('files.timeline', $file->uuid) }}"
                           class="btn btn-sm btn-outline-success" title="Timeline">
                            <i class="fa-solid fa-timeline"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $isRecordsDept ? 9 : 8 }}">
                    <div class="empty-state">
                        <i class="fa-solid fa-file-circle-question"></i>
                        No files found.
                    </div>
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($files->hasPages())
    <div class="px-4 py-3 border-top">
        {{ $files->withQueryString()->links() }}
    </div>
    @endif
</div>
@endif

@endsection
