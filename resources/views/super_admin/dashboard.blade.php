@extends('layouts.app')
@section('title', 'Super Admin Dashboard')
@section('breadcrumb')
<li class="breadcrumb-item active">Super Admin Dashboard</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">System Overview</h1>
        <div class="page-subtitle">Super Admin — Full system monitoring</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('departments.create') }}" class="btn-portal-outline"><i class="fa-solid fa-plus me-1"></i>Department</a>
        <a href="{{ route('users.create') }}"       class="btn-portal-primary"><i class="fa-solid fa-user-shield me-1"></i>Create Admin</a>
        <a href="{{ route('public.file.search') }}" class="btn-portal-outline"><i class="fa-solid fa-magnifying-glass me-1"></i>File Search</a>
    </div>
</div>

{{-- KPI ROW --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-kpi">
            <div class="stat-kpi-icon green"><i class="fa-solid fa-file-lines"></i></div>
            <div><div class="stat-kpi-label">Total Files</div><div class="stat-kpi-value">{{ $totalFiles }}</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-kpi">
            <div class="stat-kpi-icon purple"><i class="fa-solid fa-building-columns"></i></div>
            <div><div class="stat-kpi-label">Departments</div><div class="stat-kpi-value">{{ $totalDepartments }}</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-kpi">
            <div class="stat-kpi-icon green"><i class="fa-solid fa-users"></i></div>
            <div><div class="stat-kpi-label">Total Users</div><div class="stat-kpi-value">{{ $totalUsers }}</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-kpi">
            <div class="stat-kpi-icon teal"><i class="fa-solid fa-right-left"></i></div>
            <div><div class="stat-kpi-label">Total Transfers</div><div class="stat-kpi-value">{{ $totalTransfers }}</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-kpi">
            <div class="stat-kpi-icon green"><i class="fa-solid fa-user-shield"></i></div>
            <div><div class="stat-kpi-label">Admin Accounts</div><div class="stat-kpi-value">{{ $totalAdmins }}</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-kpi">
            <div class="stat-kpi-icon orange"><i class="fa-solid fa-file-circle-plus"></i></div>
            <div><div class="stat-kpi-label">Files Created</div><div class="stat-kpi-value">{{ $movementStats['created'] }}</div></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">

    {{-- Movement Stats --}}
    <div class="col-lg-4">
        <div class="portal-card h-100">
            <div class="card-header"><i class="fa-solid fa-chart-bar me-2 text-primary"></i>Movement Statistics</div>
            <div class="card-body">
                @foreach(['created' => ['orange','fa-file-circle-plus','Files Created'], 'transferred' => ['green','fa-right-left','Transferred']] as $action => $meta)
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="stat-kpi-icon {{ $meta[0] }}" style="width:32px;height:32px;border-radius:8px;font-size:.8rem;">
                            <i class="fa-solid {{ $meta[1] }}"></i>
                        </div>
                        <span class="fw-600" style="font-size:.875rem;">{{ $meta[2] }}</span>
                    </div>
                    <span class="fw-700" style="font-size:1.1rem;">{{ $movementStats[$action] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Files per Department --}}
    <div class="col-lg-8">
        <div class="portal-card h-100">
            <div class="card-header"><i class="fa-solid fa-building-columns me-2 text-primary"></i>Files per Department</div>
            <div class="card-body">
                @forelse($departmentFileCounts as $dept)
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:.845rem;font-weight:600;">{{ $dept->name }}</span>
                        <span class="fw-700" style="font-size:.845rem;">{{ $dept->files_count }}</span>
                    </div>
                    @php $max = $departmentFileCounts->max('files_count') ?: 1; $pct = round(($dept->files_count / $max) * 100); @endphp
                    <div class="progress" style="height:6px;border-radius:999px;">
                        <div class="progress-bar bg-primary" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                @empty
                <div class="empty-state"><i class="fa-solid fa-building-columns"></i>No departments found.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Recent Transfers --}}
    <div class="col-lg-6">
        <div class="portal-card">
            <div class="card-header">
                <span><i class="fa-solid fa-right-left me-2 text-primary"></i>Recent Transfers</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="portal-table">
                        <thead><tr><th>File</th><th>From</th><th>To</th><th>Dept.</th><th>Date</th></tr></thead>
                        <tbody>
                        @forelse($recentTransfers as $t)
                        <tr>
                            <td>
                                <div class="fw-700">{{ $t->file->file_name ?? 'N/A' }}</div>
                                <div class="text-muted fs-sm">{{ $t->file->file_number ?? '' }}</div>
                            </td>
                            <td>{{ $t->sender->name ?? 'System' }}</td>
                            <td>{{ $t->receiver->name ?? '—' }}</td>
                            <td class="text-muted fs-sm">{{ $t->file->department->name ?? '—' }}</td>
                            <td class="text-muted fs-sm">{{ $t->created_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-3 text-muted">No transfers yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent File Movements --}}
    <div class="col-lg-6">
        <div class="portal-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-route me-2 text-primary"></i>Recent File Movements</span>
                <a href="{{ route('admin.transfers') }}" class="btn btn-sm btn-portal-outline">View All</a>
            </div>
            <div class="card-body p-0">
                @forelse($recentMovements as $item)
                @php
                    $fileUuid = $item->file?->uuid;
                    $cardUrl  = $fileUuid ? route('admin.files.timeline', $fileUuid) : '#';
                    $isDept   = $item->fromDept && $item->toDept
                                && (int)$item->fromDept->id !== (int)$item->toDept->id;
                @endphp
                <a href="{{ $cardUrl }}" class="rfm-card {{ $isDept ? 'rfm-card-dept' : '' }}"
                   style="text-decoration:none;color:inherit;">
                    <div class="rfm-left">
                        <div class="rfm-icon rfm-icon-{{ $item->action }}">
                            @if($item->action === 'created')       <i class="fa-solid fa-file-circle-plus"></i>
                            @elseif($item->action === 'transferred')<i class="fa-solid fa-paper-plane"></i>
                            @else                                   <i class="fa-solid fa-circle-dot"></i>
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
                            <i class="fa-solid fa-quote-left fa-xs me-1"></i>{{ Str::limit($item->remarks, 60) }}
                        </div>
                        @endif
                    </div>
                    <div class="rfm-right">
                        @include('partials.action-badge', ['action' => $item->action])
                        <div class="rfm-time">{{ $item->created_at->diffForHumans() }}</div>
                    </div>
                </a>
                @empty
                <div class="empty-state py-4"><i class="fa-solid fa-inbox"></i>No activity yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
