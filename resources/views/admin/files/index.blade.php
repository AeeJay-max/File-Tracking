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
            <option value="archived"           {{ request('status') === 'archived'           ? 'selected' : '' }}>Archived</option>
        </select>

        <input type="date" name="from_date" class="form-control" style="max-width:140px;" value="{{ request('from_date') }}">
        <input type="date" name="to_date"   class="form-control" style="max-width:140px;" value="{{ request('to_date') }}">

        <button type="submit" class="btn btn-primary btn-sm px-3">
            <i class="fa-solid fa-magnifying-glass me-1"></i>Filter
        </button>
        <a href="{{ route('admin.files') }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
    </form>

    <div class="table-responsive">
        <table class="portal-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>File Number</th>
                    <th>File Name</th>
                    @if(auth()->user()->role === 'super_admin')
                    <th>Department</th>
                    @endif
                    <th>Current Holder</th>
                    <th>Previous Holder</th>
                    <th>Movements</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($files as $i => $file)
                @php
                    $prevUserId = $previousHolderIds[$file->id] ?? null;
                    $prevHolder = $prevUserId ? ($prevHolderCache[$prevUserId] ?? null) : null;
                @endphp
                <tr>
                    <td class="text-muted">{{ $files->firstItem() + $i }}</td>
                    <td><span class="fw-700 text-portal-primary">{{ $file->file_number }}</span></td>
                    <td>
                        <div class="fw-600">{{ $file->file_name }}</div>
                        @if($file->attachment_path)
                        <span class="text-muted fs-sm"><i class="fa-solid fa-paperclip me-1"></i>Attachment</span>
                        @endif
                    </td>
                    @if(auth()->user()->role === 'super_admin')
                    <td class="text-muted">{{ $file->department->name ?? 'N/A' }}</td>
                    @endif
                    <td>
                        @if($file->currentHolder)
                        <div class="d-flex align-items-center gap-2">
                            @php $holder = $file->currentHolder; @endphp
                            @if($holder->photo_url)
                            <img src="{{ $holder->photo_url }}" alt="{{ $holder->name }}"
                                 style="width:24px;height:24px;border-radius:50%;object-fit:cover;">
                            @else
                            <div style="width:24px;height:24px;border-radius:50%;background:#e8f5e9;color:#005a2b;
                                        display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;">
                                {{ $holder->initials }}
                            </div>
                            @endif
                            <span class="fs-sm">{{ $holder->name }}</span>
                        </div>
                        @elseif($file->status === 'pending_assignment')
                        <span class="badge-status badge-pending" style="font-size:.7rem;">
                            <i class="fa-solid fa-hourglass-half me-1"></i>Awaiting Assignment
                        </span>
                        @else
                        <span class="text-muted fs-sm">—</span>
                        @endif
                    </td>
                    <td class="text-muted fs-sm">{{ $prevHolder->name ?? '—' }}</td>
                    <td class="text-muted fs-sm text-center">{{ $file->movements_count ?? $file->movements->count() }}</td>
                    <td>@include('partials.status-badge', ['status' => $file->status])</td>
                    <td class="text-muted fs-sm">{{ $file->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('admin.files.timeline', $file->uuid) }}"
                           class="btn btn-sm btn-outline-primary" title="View Timeline">
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
