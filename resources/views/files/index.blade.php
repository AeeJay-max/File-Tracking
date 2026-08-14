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
            {{ auth()->user()->role === 'user' ? 'My Files' : 'Department Files' }}
        </h1>
        <div class="page-subtitle">Manage and track official documents</div>
    </div>
    @can('create', App\Models\FileRecord::class)
    <a href="{{ route('files.create') }}" class="btn-portal-primary">
        <i class="fa-solid fa-plus me-1"></i>New File
    </a>
    @endcan
</div>

{{-- ── SEARCH / FILTER BAR ─────────────────────────────────── --}}
<div class="portal-table-wrap mb-0">
    <form action="{{ route('files.index') }}" method="GET" class="table-toolbar">
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
        <a href="{{ route('files.index') }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
    </form>

    {{-- ── TABLE ──────────────────────────────────────────────── --}}
    <div class="table-responsive">
        <table class="portal-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>File Number</th>
                    <th>File Name</th>
                    <th>Department</th>
                    <th>Current Holder</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($files as $i => $file)
            <tr>
                <td class="text-muted">{{ $files->firstItem() + $i }}</td>
                <td><span class="fw-700 text-portal-primary">{{ $file->file_number }}</span></td>
                <td>{{ $file->file_name }}</td>
                <td class="text-muted">{{ $file->department->name ?? 'N/A' }}</td>
                <td>{{ $file->currentHolder->name ?? ($file->status === 'pending_assignment' ? '—' : 'N/A') }}</td>
                <td>@include('partials.status-badge', ['status' => $file->status])</td>
                <td class="text-muted fs-sm">{{ $file->created_at->format('d M Y') }}</td>
                <td>
                    <div class="d-flex gap-1 align-items-center">
                        <a href="{{ route('files.show', $file->uuid) }}"
                           class="btn btn-sm btn-outline-primary" title="View">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        {{--
                            Transfer button logic:
                            - ACTIVE + user is current holder: show transfer button
                            - Otherwise (archived, not the holder): show status indicator
                        --}}
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
                        @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                        <a href="{{ route('admin.files.timeline', $file->uuid) }}"
                           class="btn btn-sm btn-outline-success" title="Timeline">
                            <i class="fa-solid fa-timeline"></i>
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8">
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

@endsection
