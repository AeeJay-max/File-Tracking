@extends('layouts.app')
@section('title', 'Director Dashboard')
@section('breadcrumb')
<li class="breadcrumb-item active">Director Dashboard</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">User Account & System Administration</h1>
        <div class="page-subtitle">Director &mdash; User account creation, department administration, and database backups</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('users.create') }}"       class="btn-portal-primary"><i class="fa-solid fa-user-plus me-1"></i>Create User Account</a>
        <a href="{{ route('users.index') }}"        class="btn-portal-outline"><i class="fa-solid fa-users me-1"></i>Manage Users</a>
        <a href="{{ route('departments.create') }}" class="btn-portal-outline"><i class="fa-solid fa-building-columns me-1"></i>Add Department</a>
        <a href="{{ route('admin.backup.index') }}" class="btn-portal-outline"><i class="fa-solid fa-database me-1"></i>System Backups</a>
    </div>
</div>

{{-- KPI ROW --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-kpi">
            <div class="stat-kpi-icon purple"><i class="fa-solid fa-building-columns"></i></div>
            <div>
                <div class="stat-kpi-label">Active Departments</div>
                <div class="stat-kpi-value">{{ $totalDepartments }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-kpi">
            <div class="stat-kpi-icon green"><i class="fa-solid fa-users"></i></div>
            <div>
                <div class="stat-kpi-label">Total System Users</div>
                <div class="stat-kpi-value">{{ $totalUsers }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-kpi">
            <div class="stat-kpi-icon teal"><i class="fa-solid fa-user-shield"></i></div>
            <div>
                <div class="stat-kpi-label">Department Admins</div>
                <div class="stat-kpi-value">{{ $totalAdmins }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ACTION CARDS --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="portal-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-user-gear me-2 text-primary"></i>User Account Management</span>
                <a href="{{ route('users.create') }}" class="btn btn-sm btn-portal-primary"><i class="fa-solid fa-plus me-1"></i>New User</a>
            </div>
            <div class="card-body">
                <p class="text-muted fs-sm mb-3">
                    As Director / SuperAdmin, you are responsible for managing user accounts across all operational departments.
                </p>
                <div class="d-flex gap-2">
                    <a href="{{ route('users.index') }}" class="btn-portal-outline btn-sm"><i class="fa-solid fa-list me-1"></i>View All Users</a>
                    <a href="{{ route('departments.index') }}" class="btn-portal-outline btn-sm"><i class="fa-solid fa-building-columns me-1"></i>View Departments</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="portal-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-database me-2 text-primary"></i>Database &amp; Backup Control</span>
                <a href="{{ route('admin.backup.index') }}" class="btn btn-sm btn-portal-outline"><i class="fa-solid fa-download me-1"></i>Manage Backups</a>
            </div>
            <div class="card-body">
                <p class="text-muted fs-sm mb-3">
                    Generate instant SQLite database backups, view backup histories, and download or restore backup snapshots.
                </p>
                <form action="{{ route('admin.backup.create') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn-portal-primary btn-sm"><i class="fa-solid fa-box-archive me-1"></i>Create Backup Now</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
