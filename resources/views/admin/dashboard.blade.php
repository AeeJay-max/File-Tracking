@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('breadcrumb')
<li class="breadcrumb-item active">Admin Dashboard</li>
@endsection

@section('content')
@php $deptName = auth()->user()->department->name ?? 'Your Department'; @endphp

<div class="page-header">
    <div>
        <h1 class="page-title">Admin Dashboard</h1>
        <div class="page-subtitle">{{ $deptName }} &mdash; Welcome, {{ auth()->user()->name }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.files') }}" class="btn-portal-outline">
            <i class="fa-solid fa-folder-open me-1"></i>All Files
        </a>
        <a href="{{ route('public.file.search') }}" class="btn-portal-outline">
            <i class="fa-solid fa-magnifying-glass me-1"></i>File Search
        </a>
    </div>
</div>

{{-- KPI ROW --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-kpi">
            <div class="stat-kpi-icon green"><i class="fa-solid fa-file-lines"></i></div>
            <div>
                <div class="stat-kpi-label">Dept. Files</div>
                <div class="stat-kpi-value">{{ $deptFiles }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-kpi">
            <div class="stat-kpi-icon green"><i class="fa-solid fa-users"></i></div>
            <div>
                <div class="stat-kpi-label">Users in Dept.</div>
                <div class="stat-kpi-value">{{ $deptUsers }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-kpi">
            <div class="stat-kpi-icon teal"><i class="fa-solid fa-right-left"></i></div>
            <div>
                <div class="stat-kpi-label">Total Transfers</div>
                <div class="stat-kpi-value">{{ $totalTransfers }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.files.pending') }}" style="text-decoration:none;">
            <div class="stat-kpi {{ $pendingAssignments > 0 ? 'stat-kpi-alert' : '' }}">
                <div class="stat-kpi-icon {{ $pendingAssignments > 0 ? 'orange' : 'grey' }}">
                    <i class="fa-solid fa-inbox"></i>
                </div>
                <div>
                    <div class="stat-kpi-label">Awaiting Assignment</div>
                    <div class="stat-kpi-value">{{ $pendingAssignments }}</div>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- INCOMING DEPARTMENT FILES (pending assignment) --}}
@if($pendingAssignments > 0)
<div class="portal-card mb-4" style="border-left: 4px solid #f59e0b;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="fa-solid fa-inbox me-2 text-warning"></i>
            <strong>Incoming Department Files</strong>
            <span class="badge bg-warning text-dark ms-2">{{ $pendingAssignments }}</span>
        </span>
        <a href="{{ route('admin.files.pending') }}" class="btn btn-sm btn-warning">
            <i class="fa-solid fa-user-plus me-1"></i>Assign All
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>File Number</th>
                        <th>File Name</th>
                        <th>Created By</th>
                        <th>Received</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingFiles as $pf)
                    <tr>
                        <td class="fw-700 text-portal-primary">{{ $pf->file_number }}</td>
                        <td>{{ $pf->file_name }}</td>
                        <td class="text-muted fs-sm">{{ $pf->creator->name ?? '—' }}</td>
                        <td class="text-muted fs-sm">{{ $pf->updated_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.files.pending') }}"
                               class="btn btn-sm btn-warning">
                                <i class="fa-solid fa-user-plus me-1"></i>Assign
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<div class="row g-3 mb-4">

    {{-- Recent Files --}}
    <div class="col-lg-7">
        <div class="portal-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-file-lines me-2 text-primary"></i>Department Files</span>
                <a href="{{ route('admin.files') }}" class="btn btn-sm btn-portal-outline">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>File Number</th>
                                <th>Name</th>
                                <th>Current Holder</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentFiles as $f)
                            <tr>
                                <td class="text-muted fs-sm">{{ $f->file_number }}</td>
                                <td class="fw-700">{{ $f->file_name }}</td>
                                <td class="text-muted">
                                    @if($f->currentHolder)
                                        {{ $f->currentHolder->name }}
                                    @else
                                        <span class="badge-status badge-pending" style="font-size:.7rem;">
                                            <i class="fa-solid fa-hourglass-half me-1"></i>Awaiting Assignment
                                        </span>
                                    @endif
                                </td>
                                <td>@include('partials.status-badge', ['status' => $f->status])</td>
                                <td>
                                    <a href="{{ route('admin.files.timeline', $f->uuid) }}"
                                       class="btn btn-sm btn-portal-outline" title="Timeline">
                                        <i class="fa-solid fa-timeline"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-3 text-muted">No files found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Users in Department --}}
    <div class="col-lg-5">
        <div class="portal-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-users me-2 text-primary"></i>Users in {{ $deptName }}</span>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-portal-outline">Manage</a>
            </div>
            <div class="card-body p-0">
                @forelse($recentUsers as $u)
                <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                    <div style="width:32px;height:32px;border-radius:50%;background:#e8f5e9;color:#005a2b;
                                display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;flex-shrink:0;">
                        {{ strtoupper(substr($u->name, 0, 1)) }}
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-700 text-truncate" style="font-size:.845rem;">{{ $u->name }}</div>
                        <div class="text-muted fs-sm">{{ $u->designation->name ?? 'No Designation' }}</div>
                    </div>
                </div>
                @empty
                <div class="empty-state"><i class="fa-solid fa-users"></i>No users found.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>

{{-- Recent File Movements — Modern cards --}}
<div class="portal-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-route me-2 text-primary"></i>Recent File Movements</span>
        <a href="{{ route('admin.transfers') }}" class="btn btn-sm btn-portal-outline">View All</a>
    </div>
    <div class="card-body p-0">
        @forelse($recentActivity as $item)
        @php
            $fileUuid = $item->file?->uuid;
            $cardUrl  = $fileUuid ? route('admin.files.timeline', $fileUuid) : '#';
            $isDept   = $item->fromDept && $item->toDept
                        && (int)$item->fromDept->id !== (int)$item->toDept->id;
        @endphp
        <a href="{{ $cardUrl }}"
           class="rfm-card {{ $isDept ? 'rfm-card-dept' : '' }}"
           style="text-decoration:none;color:inherit;">
            <div class="rfm-left">
                {{-- Action icon --}}
                <div class="rfm-icon rfm-icon-{{ $item->action }}">
                    @if($item->action === 'created')      <i class="fa-solid fa-file-circle-plus"></i>
                    @elseif($item->action === 'transferred')<i class="fa-solid fa-paper-plane"></i>
                    @else                                  <i class="fa-solid fa-circle-dot"></i>
                    @endif
                </div>
            </div>
            <div class="rfm-body">
                <div class="rfm-file">
                    <span class="rfm-file-num">{{ $item->file?->file_number ?? 'N/A' }}</span>
                    <span class="rfm-file-name text-truncate">{{ $item->file?->file_name ?? '' }}</span>
                </div>
                <div class="rfm-flow">
                    <span class="rfm-person">
                        <i class="fa-solid fa-user fa-xs me-1"></i>{{ $item->fromUser?->name ?? 'System' }}
                        <span class="rfm-dept-badge">{{ $item->fromDept?->name ?? '' }}</span>
                    </span>
                    <span class="rfm-arrow"><i class="fa-solid fa-arrow-right fa-xs"></i></span>
                    <span class="rfm-person {{ $isDept ? 'rfm-dept-node' : '' }}">
                        @if($isDept)
                        <i class="fa-solid fa-building-columns fa-xs me-1"></i>{{ $item->toDept?->name ?? '—' }}
                        @else
                        <i class="fa-solid fa-user fa-xs me-1"></i>{{ $item->toUser?->name ?? '—' }}
                        <span class="rfm-dept-badge">{{ $item->toDept?->name ?? '' }}</span>
                        @endif
                    </span>
                </div>
                @if($item->remarks)
                <div class="rfm-remarks">
                    <i class="fa-solid fa-quote-left fa-xs me-1"></i>{{ Str::limit($item->remarks, 70) }}
                </div>
                @endif
            </div>
            <div class="rfm-right">
                @include('partials.action-badge', ['action' => $item->action])
                <div class="rfm-time">{{ $item->created_at->diffForHumans() }}</div>
            </div>
        </a>
        @empty
        <div class="empty-state py-4">
            <i class="fa-solid fa-inbox"></i>No file movements yet.
        </div>
        @endforelse
    </div>
</div>

@endsection
