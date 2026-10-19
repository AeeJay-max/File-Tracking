@extends('layouts.app')
@section('title', 'Department Files')

@section('breadcrumb')
<li class="breadcrumb-item active">Files</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            @if(auth()->user()->role === 'super_admin') All Files
            @else {{ auth()->user()->department->name ?? 'Department' }} Files
            @endif
        </h1>
        <div class="page-subtitle">View file movements and transfer history</div>
    </div>
</div>

{{-- Filter Bar --}}
<div class="portal-table-wrap mb-0">
    <form action="{{ route('admin.files') }}" method="GET" class="table-toolbar">
        <input type="text" name="search" class="form-control" style="max-width:220px;min-width:160px;"
            placeholder="File name or number..." value="{{ request('search') }}">

        @if(auth()->user()->role === 'super_admin')
        <select name="department_id" class="form-select" style="max-width:200px;">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
            <option value="{{ $dept->uuid }}" {{ request('department_id') == $dept->uuid ? 'selected' : '' }}>
                {{ $dept->name }}
            </option>
            @endforeach
        </select>
        @endif

        <select name="status" class="form-select" style="max-width:145px;">
            <option value="">All Statuses</option>
            <option value="active"             {{ request('status') === 'active'             ? 'selected' : '' }}>Active</option>
            <option value="pending_assignment" {{ request('status') === 'pending_assignment' ? 'selected' : '' }}>Awaiting Assignment</option>
            <option value="completed"          {{ request('status') === 'completed'          ? 'selected' : '' }}>Completed / Done</option>
            <option value="archived"           {{ request('status') === 'archived'           ? 'selected' : '' }}>Archived</option>
        </select>

        <input type="date" name="from_date" class="form-control" style="max-width:140px;" value="{{ request('from_date') }}">
        <input type="date" name="to_date"   class="form-control" style="max-width:140px;" value="{{ request('to_date') }}">

        <button type="submit" class="btn btn-primary btn-sm px-3">
            <i class="fa-solid fa-magnifying-glass me-1"></i>Filter
        </button>
        <a href="{{ route('admin.files') }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
    </form>

@push('styles')
<style>
    /* Scoped layout rules for Records Files Table */
    .admin-files-table {
        table-layout: fixed;
        width: 100%;
        min-width: 1060px;
        border-collapse: collapse;
    }

    .admin-files-table thead th,
    .admin-files-table tbody td {
        vertical-align: middle;
        box-sizing: border-box;
    }

    /* Col 1: # */
    .admin-files-table .col-num {
        width: 48px;
        min-width: 48px;
        max-width: 48px;
        text-align: center;
        padding-left: 6px;
        padding-right: 6px;
    }

    /* Col 2: Folio Number */
    .admin-files-table .col-folio {
        width: 125px;
        min-width: 120px;
        white-space: nowrap;
    }

    /* Col 3: File Name */
    .admin-files-table .col-name {
        width: 21%;
        min-width: 170px;
        word-break: break-word;
        overflow-wrap: anywhere;
        line-height: 1.35;
    }

    @if(auth()->user()->role === 'super_admin')
    /* Col: Department (Super Admin only) */
    .admin-files-table .col-dept {
        width: 14%;
        min-width: 130px;
        word-break: break-word;
        line-height: 1.35;
    }
    @endif

    /* Col 4: Current Holder */
    .admin-files-table .col-holder {
        width: 18%;
        min-width: 165px;
        line-height: 1.35;
    }

    .admin-files-table .col-holder .badge-status {
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        padding: 3px 8px;
        font-size: .72rem;
    }

    /* Col 5: Previous Holder */
    .admin-files-table .col-prev-holder {
        width: 14%;
        min-width: 130px;
        white-space: normal;
        word-break: break-word;
        overflow-wrap: break-word;
        line-height: 1.35;
    }

    /* Col 6: Movements */
    .admin-files-table .col-movements {
        width: 75px;
        min-width: 75px;
        max-width: 75px;
        text-align: center;
        padding-left: 4px;
        padding-right: 4px;
    }

    /* Col 7: Status */
    .admin-files-table .col-status {
        width: 145px;
        min-width: 140px;
        text-align: center;
        padding-left: 6px;
        padding-right: 6px;
    }

    .admin-files-table .col-status .badge-status {
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        padding: 3px 9px;
        font-size: .74rem;
    }

    /* Col 8: Created */
    .admin-files-table .col-created {
        width: 105px;
        min-width: 100px;
        text-align: center;
        white-space: nowrap;
        padding-left: 6px;
        padding-right: 6px;
    }

    /* Col 9: Actions */
    .admin-files-table .col-actions {
        width: 110px;
        min-width: 105px;
        text-align: center;
        padding-left: 6px;
        padding-right: 6px;
    }

    .admin-files-table .timeline-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        padding: 4px 10px;
        font-size: .8rem;
    }
</style>
@endpush

    <div class="table-responsive">
        <table class="portal-table admin-files-table">
            <colgroup>
                <col class="col-num d-none d-md-table-cell">
                <col class="col-folio">
                <col class="col-name">
                @if(auth()->user()->role === 'super_admin')
                <col class="col-dept d-none d-lg-table-cell">
                @endif
                <col class="col-holder">
                <col class="col-prev-holder d-none d-lg-table-cell">
                <col class="col-movements d-none d-md-table-cell">
                <col class="col-status">
                <col class="col-created d-none d-md-table-cell">
                <col class="col-actions">
            </colgroup>
            <thead>
                <tr>
                    <th class="col-num d-none d-md-table-cell">#</th>
                    <th class="col-folio">Folio Number</th>
                    <th class="col-name">File Name</th>
                    @if(auth()->user()->role === 'super_admin')
                    <th class="col-dept d-none d-lg-table-cell">Department</th>
                    @endif
                    <th class="col-holder">Current Holder</th>
                    <th class="col-prev-holder d-none d-lg-table-cell">Previous Holder</th>
                    <th class="col-movements d-none d-md-table-cell">Movements</th>
                    <th class="col-status">Status</th>
                    <th class="col-created d-none d-md-table-cell">Created</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($files as $i => $file)
                @php
                    $prevUserId = $previousHolderIds[$file->id] ?? null;
                    $prevHolder = $prevUserId ? ($prevHolderCache[$prevUserId] ?? null) : null;
                @endphp
                <tr>
                    <td class="col-num text-muted d-none d-md-table-cell">{{ $files->firstItem() + $i }}</td>
                    <td class="col-folio"><span class="fw-700 text-portal-primary">{{ $file->file_number }}</span></td>
                    <td class="col-name">
                        <div class="fw-600">{{ $file->file_name }}</div>
                        @if($file->attachment_path)
                        <span class="text-muted fs-sm d-inline-flex align-items-center mt-1"><i class="fa-solid fa-paperclip me-1"></i>Attachment</span>
                        @endif
                    </td>
                    @if(auth()->user()->role === 'super_admin')
                    <td class="col-dept text-muted d-none d-lg-table-cell">{{ $file->department->name ?? 'N/A' }}</td>
                    @endif
                    <td class="col-holder">
                        @if($file->status === 'completed')
                        <span class="badge-status badge-completed">
                            <i class="fa-solid fa-circle-check me-1"></i>Completed / Done
                        </span>
                        @elseif($file->currentHolder)
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            @php $holder = $file->currentHolder; @endphp
                            @if($holder->photo_url)
                            <img src="{{ $holder->photo_url }}" alt="{{ $holder->name }}"
                                 style="width:24px;height:24px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                            @else
                            <div style="width:24px;height:24px;border-radius:50%;background:#e8f5e9;color:#005a2b;
                                        display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;flex-shrink:0;">
                                {{ $holder->initials }}
                            </div>
                            @endif
                            <span class="fs-sm fw-600 text-truncate" title="{{ $holder->name }}">{{ $holder->name }}</span>
                        </div>
                        @elseif($file->status === 'pending_assignment')
                        <span class="badge-status badge-pending">
                            <i class="fa-solid fa-hourglass-half me-1"></i>Awaiting Assignment
                        </span>
                        @else
                        <span class="text-muted fs-sm">—</span>
                        @endif
                    </td>
                    <td class="col-prev-holder text-muted fs-sm d-none d-lg-table-cell">{{ $prevHolder->name ?? '—' }}</td>
                    <td class="col-movements text-muted fs-sm d-none d-md-table-cell">{{ $file->movements_count ?? $file->movements->count() }}</td>
                    <td class="col-status">@include('partials.status-badge', ['status' => $file->status])</td>
                    <td class="col-created text-muted fs-sm d-none d-md-table-cell">{{ $file->created_at->format('d M Y') }}</td>
                    <td class="col-actions">
                        <a href="{{ route('files.timeline', $file->uuid) }}"
                           class="btn btn-sm btn-outline-primary timeline-btn" title="View Timeline">
                            <i class="fa-solid fa-timeline me-1"></i>Timeline
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">
                        <div class="empty-state"><i class="fa-solid fa-file-circle-question"></i>No files found.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($files->hasPages())
    <div class="px-4 py-3 border-top">{{ $files->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
