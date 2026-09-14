@extends('layouts.app')

@section('title', 'Chief Director Dashboard')

@section('breadcrumb')
<li class="breadcrumb-item active">Chief Director Dashboard</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Chief Director Dashboard</h1>
        <div class="page-subtitle">Executive Operations &amp; Director Oversight &mdash; Welcome, {{ auth()->user()->name }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('chief_director.directors.index') }}" class="btn-portal-outline">
            <i class="fa-solid fa-users me-1"></i>Manage Directors
        </a>
        <a href="{{ route('public.file.search') }}" class="btn-portal-outline">
            <i class="fa-solid fa-magnifying-glass me-1"></i>Search Files
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4 p-3 shadow-sm" style="border-radius:12px;background:#ecfdf5;border:1px solid #10b981;color:#065f46;">
    <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4 p-3 shadow-sm" style="border-radius:12px;background:#fef2f2;border:1px solid #ef4444;color:#991b1b;">
    <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- KPI ROW --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-kpi">
            <div class="stat-kpi-icon teal"><i class="fa-solid fa-user-tie"></i></div>
            <div>
                <div class="stat-kpi-label">Departmental Directors</div>
                <div class="stat-kpi-value">{{ $stats['total_directors'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-kpi">
            <div class="stat-kpi-icon green"><i class="fa-solid fa-user-check"></i></div>
            <div>
                <div class="stat-kpi-label">Active Directors</div>
                <div class="stat-kpi-value">{{ $stats['active_directors'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-kpi {{ $stats['active_assignments'] > 0 ? 'stat-kpi-alert' : '' }}">
            <div class="stat-kpi-icon {{ $stats['active_assignments'] > 0 ? 'orange' : 'grey' }}">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div>
                <div class="stat-kpi-label">Acting PermSec Delegations</div>
                <div class="stat-kpi-value">{{ $stats['active_assignments'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-kpi">
            <div class="stat-kpi-icon blue"><i class="fa-solid fa-circle-check"></i></div>
            <div>
                <div class="stat-kpi-label">Completed Delegated Actions</div>
                <div class="stat-kpi-value">{{ $stats['completed_assignments'] }}</div>
            </div>
        </div>
    </div>
</div>

{{-- SECTION A: ASSIGNED FILES (ACTING PERMSEC DELEGATIONS) --}}
<div class="portal-card mb-4" style="border-left: 4px solid #005a2b;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="fa-solid fa-user-shield me-2 text-primary"></i>
            <strong>Files Assigned by Permanent Secretary (Acting PermSec Authority)</strong>
            <span class="badge bg-primary ms-2">{{ $activeAssignments->count() }} Active</span>
        </span>
    </div>
    <div class="card-body p-0">
        @if($activeAssignments->count() > 0)
        <div class="table-responsive">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>File Number</th>
                        <th>File Name</th>
                        <th>Assigned By</th>
                        <th>Assigned At</th>
                        <th>Return Destination</th>
                        <th>Authority Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeAssignments as $assignment)
                    @php $file = $assignment->file; @endphp
                    <tr>
                        <td class="fw-700 text-portal-primary">
                            <a href="{{ route('chief_director.files.show', $file->uuid) }}">{{ $file->file_number }}</a>
                            @if($file->is_urgent)
                                <span class="badge bg-danger ms-1">Urgent</span>
                            @endif
                        </td>
                        <td class="fw-600">{{ $file->file_name }}</td>
                        <td>
                            <div class="fw-600">{{ $assignment->assignedBy->name ?? 'Permanent Secretary' }}</div>
                            <small class="text-muted">Permanent Secretary</small>
                        </td>
                        <td class="fs-sm">{{ $assignment->assigned_at->format('d M Y, h:i A') }}</td>
                        <td>
                            <span class="badge {{ $assignment->return_destination === 'permsec_office' ? 'bg-primary' : 'bg-secondary' }}">
                                <i class="fa-solid {{ $assignment->return_destination === 'permsec_office' ? 'fa-building-user' : 'fa-folder' }} me-1"></i>
                                {{ $assignment->return_destination === 'permsec_office' ? "PermSec's Office" : "Records Dept" }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-success"><i class="fa-solid fa-shield-halved me-1"></i>Acting PermSec Active</span>
                        </td>
                        <td>
                            <a href="{{ route('chief_director.files.show', $file->uuid) }}" class="btn-portal-primary btn-sm">
                                <i class="fa-solid fa-pen-to-square me-1"></i>Review &amp; Action
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
            <h5>No Active Delegated Files</h5>
            <p class="mb-0">Files assigned to you by the Permanent Secretary with Acting Permanent Secretary authority will appear here.</p>
        </div>
        @endif
    </div>
</div>

{{-- SECTION B: DIRECTOR OVERSIGHT --}}
<div class="portal-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="fa-solid fa-users me-2 text-primary"></i>
            <strong>Departmental Directors Oversight</strong>
            <span class="badge bg-secondary ms-2">{{ $directors->count() }} Total</span>
        </span>
        <a href="{{ route('chief_director.directors.index') }}" class="btn btn-sm btn-portal-outline">
            View All Directors <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>Director Name</th>
                        <th>Display Title</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($directors->take(10) as $dir)
                    <tr>
                        <td class="fw-700">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;border-radius:50%;background:#e8f5e9;color:#005a2b;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.75rem;">
                                    {{ $dir->initials }}
                                </div>
                                <div>
                                    <div>{{ $dir->name }}</div>
                                    <small class="text-muted">{{ $dir->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="fw-600 text-portal-primary">
                            <i class="fa-solid fa-user-tie me-1"></i>{{ $dir->display_title }}
                        </td>

                        <td>{{ $dir->department->name ?? 'Unassigned' }}</td>
                        <td>
                            @if($dir->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('chief_director.directors.show', $dir->uuid) }}" class="btn btn-sm btn-outline-primary" title="View Director Profile">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('chief_director.directors.edit', $dir->uuid) }}" class="btn btn-sm btn-outline-secondary" title="Edit Director Account">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('chief_director.directors.toggle-status', $dir->uuid) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $dir->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}" title="{{ $dir->is_active ? 'Deactivate Account' : 'Activate Account' }}">
                                        <i class="fa-solid {{ $dir->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No Departmental Directors registered yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- RECENT COMPLETED DELEGATIONS HISTORY --}}
@if($completedAssignments->count() > 0)
<div class="portal-card">
    <div class="card-header">
        <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Recent Completed Delegated Actions
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Assigned By</th>
                        <th>Completed At</th>
                        <th>Action Summary</th>
                        <th>Returned To</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($completedAssignments as $cAssign)
                    <tr>
                        <td class="fw-700">
                            <a href="{{ route('chief_director.files.show', $cAssign->file->uuid) }}">{{ $cAssign->file->file_number }}</a>
                        </td>
                        <td>{{ $cAssign->assignedBy->name ?? 'Permanent Secretary' }}</td>
                        <td class="fs-sm">{{ $cAssign->completed_at?->format('d M Y, h:i A') }}</td>
                        <td class="text-break fs-sm">{{ Str::limit($cAssign->required_action ?? 'Action completed', 80) }}</td>
                        <td>
                            <span class="badge bg-secondary">
                                {{ $cAssign->return_destination === 'permsec_office' ? "PermSec's Office" : "Records Dept" }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
