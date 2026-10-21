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

        <select name="status" class="form-select" style="max-width:170px;min-width:140px;">
            <option value="">All Statuses</option>
            <option value="overdue"            {{ request('status') === 'overdue'            ? 'selected' : '' }}>⚠️ Overdue (>8 Hours)</option>
            <option value="active"             {{ request('status') === 'active'             ? 'selected' : '' }}>Active</option>
            <option value="pending_assignment" {{ request('status') === 'pending_assignment' ? 'selected' : '' }}>Awaiting Assignment</option>
            <option value="completed"          {{ request('status') === 'completed'          ? 'selected' : '' }}>Completed / Done</option>
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
@push('styles')
<style>
    /* Scoped layout rules for Folder Files Table (Director — Records) */
    .folder-files-table {
        table-layout: fixed;
        width: 100%;
        min-width: 1180px;
        border-collapse: collapse;
    }

    .folder-files-table thead th,
    .folder-files-table tbody td {
        vertical-align: middle;
        box-sizing: border-box;
    }

    /* Col 1: # */
    .folder-files-table .col-num {
        width: 42px;
        min-width: 42px;
        max-width: 42px;
        text-align: center;
        padding-left: 4px;
        padding-right: 4px;
    }

    /* Col 2: File & Folder */
    .folder-files-table .col-file-folder {
        width: 125px;
        min-width: 120px;
        line-height: 1.35;
    }

    /* Col 3: File Name */
    .folder-files-table .col-file-name {
        min-width: 170px;
        line-height: 1.35;
    }

    .folder-files-table .file-name-text {
        font-weight: 600;
        color: #0f172a;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    /* Col 4: Origin Dept */
    .folder-files-table .col-origin-dept {
        min-width: 110px;
        white-space: normal;
        word-break: break-word;
        overflow-wrap: break-word;
        line-height: 1.35;
    }

    /* Col 5: Current Holder / Dept */
    .folder-files-table .col-holder-dept {
        min-width: 155px;
        line-height: 1.35;
    }

    .folder-files-table .holder-dept-wrap {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
        min-width: 0;
    }

    .folder-files-table .holder-dept-badge {
        display: inline-block;
        max-width: 100%;
        white-space: normal;
        word-break: break-word;
        line-height: 1.25;
        border-radius: 6px;
        font-size: .74rem;
        padding: 2px 7px;
    }

    .folder-files-table .holder-person {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        max-width: 100%;
        font-size: .78rem;
        line-height: 1.25;
        word-break: break-word;
    }

    /* Col 6: Assigned Admin & Officer */
    .folder-files-table .col-assigned {
        min-width: 145px;
        line-height: 1.35;
    }

    .folder-files-table .assigned-info-wrap {
        font-size: .78rem;
        line-height: 1.35;
    }

    .folder-files-table .assigned-row {
        white-space: normal;
        word-break: break-word;
        overflow-wrap: break-word;
    }

    /* Col 7: Status & Urgency */
    .folder-files-table .col-status-urgency {
        min-width: 140px;
        line-height: 1.35;
    }

    .folder-files-table .status-urgency-wrap {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
        min-width: 0;
    }

    .folder-files-table .status-urgency-wrap .badge-status {
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        font-size: .74rem;
        padding: 3px 8px;
    }

    .folder-files-table .sub-badge {
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        font-size: .72rem;
        padding: 2px 7px;
    }

    /* Col 8: Created */
    .folder-files-table .col-created {
        width: 95px;
        min-width: 95px;
        max-width: 95px;
        text-align: center;
        white-space: nowrap;
        padding-left: 4px;
        padding-right: 4px;
    }

    /* Col 9: Actions */
    .folder-files-table .col-actions {
        width: 195px;
        min-width: 190px;
        padding-left: 6px;
        padding-right: 6px;
    }

    .folder-files-table .actions-wrap {
        display: flex;
        align-items: center;
        gap: 4px;
        flex-wrap: nowrap;
        white-space: nowrap;
    }

    .folder-files-table .actions-wrap .btn {
        padding: 4px 7px;
        font-size: .78rem;
        white-space: nowrap;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .folder-files-table .actions-wrap .btn-icon-only {
        width: 30px;
        height: 30px;
        padding: 0;
    }

    .folder-files-table .actions-wrap .badge-status {
        padding: 3px 7px;
        font-size: .74rem;
        white-space: nowrap;
        flex-shrink: 0;
    }
</style>
@endpush

    <div class="table-responsive">
        <table class="portal-table folder-files-table">
            @if($isRecordsDept)
            <colgroup>
                <col class="col-num" style="width: 42px;">
                <col class="col-file-folder" style="width: 125px;">
                <col class="col-file-name" style="width: 20%;">
                <col class="col-origin-dept" style="width: 12%;">
                <col class="col-holder-dept" style="width: 16%;">
                <col class="col-assigned" style="width: 15%;">
                <col class="col-status-urgency" style="width: 14%;">
                <col class="col-created" style="width: 95px;">
                <col class="col-actions" style="width: 195px;">
            </colgroup>
            @else
            <colgroup>
                <col class="col-num" style="width: 42px;">
                <col class="col-file-folder" style="width: 130px;">
                <col class="col-file-name" style="width: 24%;">
                <col class="col-origin-dept" style="width: 15%;">
                <col class="col-holder-dept" style="width: 19%;">
                <col class="col-status-urgency" style="width: 16%;">
                <col class="col-created" style="width: 95px;">
                <col class="col-actions" style="width: 160px;">
            </colgroup>
            @endif
            <thead>
                <tr>
                    <th class="col-num text-center">#</th>
                    <th class="col-file-folder">File &amp; Folder</th>
                    <th class="col-file-name">File Name</th>
                    <th class="col-origin-dept">Origin Dept</th>
                    <th class="col-holder-dept">Current Holder<br>/ Dept</th>
                    @if($isRecordsDept)
                    <th class="col-assigned">Assigned Admin<br>&amp; Officer</th>
                    @endif
                    <th class="col-status-urgency">Status &amp;<br>Urgency</th>
                    <th class="col-created text-center">Created</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($files as $i => $file)
            @php
                $cDeptCode = strtoupper((string) ($file->currentDepartment?->code ?? ''));
                $cDeptName = \Illuminate\Support\Str::lower((string) ($file->currentDepartment?->name ?? ''));
                $isInRecords = ($cDeptCode === 'REC' || \Illuminate\Support\Str::contains($cDeptName, 'record'));
                $isUrgent = (bool) $file->is_urgent && ! $isInRecords;
                $hasDeadline = $file->return_deadline !== null && ! $isInRecords;
                $isOverdue = $file->isOverdue() && ! $isInRecords;
            @endphp
            <tr class="{{ $isUrgent || $isOverdue ? 'table-danger border-start border-danger border-4' : '' }}">
                <td class="col-num text-muted text-center fw-600">{{ $files->firstItem() + $i }}</td>
                <td class="col-file-folder">
                    <span class="fw-700 text-portal-primary d-block">{{ $file->file_number }}</span>
                    @if($file->folder)
                    <div class="mt-1">
                        <span class="badge bg-secondary bg-opacity-10 text-dark border d-inline-flex align-items-center fs-xs">
                            <i class="fa-solid fa-folder me-1 text-primary"></i>{{ $file->folder->folder_number }}
                        </span>
                        <div class="text-muted fs-xs text-truncate mt-0.5" style="max-width:130px;" title="{{ $file->folder->folder_name }}">
                            {{ $file->folder->folder_name }}
                        </div>
                    </div>
                    @endif
                </td>
                <td class="col-file-name">
                    <div class="file-name-text">{{ $file->file_name }}</div>
                    @if($isUrgent)
                    <span class="badge bg-danger text-white mt-1 sub-badge">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>URGENT
                    </span>
                    @endif
                </td>
                <td class="col-origin-dept text-muted">{{ $file->department->name ?? 'N/A' }}</td>

                {{-- CURRENT HOLDER (Includes Department Name) --}}
                <td class="col-holder-dept">
                    <div class="holder-dept-wrap">
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-600 holder-dept-badge">
                            <i class="fa-solid fa-building-columns me-1"></i>{{ $file->currentDepartment->name ?? 'N/A' }}
                        </span>
                        @if($file->status === 'completed')
                        <small class="text-success fw-600 holder-person">
                            <i class="fa-solid fa-circle-check me-1"></i>Completed / Done
                        </small>
                        @elseif($file->currentHolder)
                        <small class="text-dark fw-600 holder-person">
                            <i class="fa-solid fa-user me-1 text-secondary"></i>{{ $file->currentHolder->name }}
                        </small>
                        @elseif($file->status === 'pending_assignment')
                        <small class="text-warning fw-600 holder-person">
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
                <td class="col-assigned">
                    @if($lastAssignMovement)
                    <div class="assigned-info-wrap">
                        <div class="assigned-row text-muted">Admin: <strong class="text-dark">{{ $lastAssignMovement->fromUser->name ?? 'System' }}</strong></div>
                        <div class="assigned-row text-muted mt-1">Assigned: <strong class="text-dark">{{ $lastAssignMovement->toUser->name ?? ($file->currentDepartment->name ?? 'Department') }}</strong></div>
                    </div>
                    @else
                    <span class="text-muted fs-xs">—</span>
                    @endif
                </td>
                @endif

                <td class="col-status-urgency">
                    <div class="status-urgency-wrap">
                        @include('partials.status-badge', ['status' => $file->status])

                        @if($isOverdue)
                        <span class="badge bg-danger text-white sub-badge" title="Held for over {{ $file->hoursWithCurrentHolder() }} hours in non-records department">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i>Overdue ({{ $file->hoursWithCurrentHolder() }}h)
                        </span>
                        @elseif($hasDeadline && $file->status !== 'completed' && $file->status !== 'archived')
                        <span class="badge bg-warning text-dark sub-badge">
                            <i class="fa-solid fa-hourglass-half me-1"></i>Return in {{ $file->return_deadline->diffForHumans(['parts' => 2]) }}
                        </span>
                        @endif
                    </div>
                </td>

                <td class="col-created text-muted fs-sm text-center">{{ $file->created_at->format('d M Y') }}</td>
                <td class="col-actions">
                    <div class="actions-wrap">
                        <a href="{{ route('files.show', $file->uuid) }}"
                           class="btn btn-sm btn-outline-primary btn-icon-only" title="View">
                            <i class="fa-solid fa-eye"></i>
                        </a>

                        @if($isRecordsDept && $isOverdue)
                            <form action="{{ route('files.pingOverdue', $file->uuid) }}" method="POST" class="d-inline m-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Ping File (Send Overdue Notice to Holder & HOD)" onclick="return confirm('Ping file {{ $file->file_number }}? This will send an urgent notification to the file holder and Department HOD.')">
                                    <i class="fa-solid fa-bell me-1"></i>Ping
                                </button>
                            </form>
                        @endif

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
                           class="btn btn-sm btn-outline-success btn-icon-only" title="Timeline">
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
