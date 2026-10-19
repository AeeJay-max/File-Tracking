@extends('layouts.app')
@section('title', 'User & Officer Management')

@section('breadcrumb')
<li class="breadcrumb-item active">User Management</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">User &amp; Officer Management</h1>
        <div class="page-subtitle">Create and manage Departmental Admin and User accounts</div>
    </div>
    <a href="{{ route('users.create') }}" class="btn-portal-primary">
        <i class="fa-solid fa-user-plus"></i> Create User Account
    </a>
</div>

<div class="portal-table-wrap mb-3">
    <form method="GET" class="table-toolbar">
        <input type="text" name="search" class="form-control" style="max-width:200px;"
            placeholder="Search name or email..." value="{{ request('search') }}">
        <select name="role" class="form-select" style="max-width:180px;">
            <option value="">All Roles</option>
            <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
            <option value="chief_director" {{ request('role') === 'chief_director' ? 'selected' : '' }}>Chief Director</option>
            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Director (HOD)</option>
            <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
        </select>
        <select name="department_id" class="form-select" style="max-width:200px;">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary btn-sm px-3">
            <i class="fa-solid fa-magnifying-glass me-1"></i>Filter
        </button>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
    </form>
</div>

@push('styles')
<style>
    /* Super Admin User & Officer Management Table — Scoped Layout Styles */
    .super-admin-users-table {
        table-layout: fixed;
        width: 100%;
        min-width: 950px;
        border-collapse: collapse;
    }

    .super-admin-users-table thead th,
    .super-admin-users-table tbody td {
        vertical-align: middle;
        box-sizing: border-box;
    }

    /* Col 1: # Row Number */
    .super-admin-users-table .col-num {
        width: 48px;
        min-width: 48px;
        max-width: 48px;
        text-align: center;
        padding-left: 6px;
        padding-right: 6px;
    }

    /* Col 2: Name & Title */
    .super-admin-users-table .col-name {
        width: 26%;
        min-width: 220px;
    }

    .super-admin-users-table .user-info-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .super-admin-users-table .user-text-wrap {
        min-width: 0;
        flex: 1 1 auto;
        overflow: hidden;
    }

    .super-admin-users-table .user-name {
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
        margin-bottom: 3px;
        word-break: break-word;
    }

    .super-admin-users-table .user-badge-wrap {
        display: block;
        line-height: 1.2;
    }

    .super-admin-users-table .user-badge-wrap .badge-status {
        display: inline-block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
        line-height: 1.2;
        padding: 3px 8px;
        font-size: .74rem;
        margin-top: 2px;
    }

    /* Col 3: Email */
    .super-admin-users-table .col-email {
        width: 22%;
        min-width: 190px;
        overflow-wrap: anywhere;
        word-break: break-word;
        line-height: 1.4;
    }

    /* Col 4: Department */
    .super-admin-users-table .col-dept {
        width: 21%;
        min-width: 165px;
        white-space: normal;
        word-break: normal;
        overflow-wrap: break-word;
        line-height: 1.4;
    }

    /* Col 5: Designation */
    .super-admin-users-table .col-desig {
        width: 21%;
        min-width: 160px;
        white-space: normal;
        word-break: normal;
        overflow-wrap: break-word;
        line-height: 1.4;
    }

    /* Col 6: Actions */
    .super-admin-users-table .col-actions {
        width: 86px;
        min-width: 86px;
        max-width: 86px;
        text-align: center;
        padding-left: 6px;
        padding-right: 6px;
    }

    .super-admin-users-table .action-btn-group {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
    }

    .super-admin-users-table .action-btn-group .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .8rem;
        flex-shrink: 0;
    }
</style>
@endpush

<div class="portal-table-wrap">
    <div class="table-responsive">
        <table class="portal-table super-admin-users-table">
            <colgroup>
                <col class="col-num">
                <col class="col-name">
                <col class="col-email">
                <col class="col-dept">
                <col class="col-desig">
                <col class="col-actions">
            </colgroup>
            <thead>
                <tr>
                    <th class="col-num">#</th>
                    <th class="col-name">Name &amp; Title</th>
                    <th class="col-email">Email</th>
                    <th class="col-dept">Department</th>
                    <th class="col-desig">Designation</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="col-num text-muted fw-600">{{ $loop->iteration }}</td>
                    <td class="col-name">
                        <div class="user-info-wrap">
                            <x-user-avatar :user="$user" :size="32" />
                            <div class="user-text-wrap">
                                <div class="user-name">{{ $user->name }}</div>
                                <div class="user-badge-wrap">
                                    <span class="badge-status badge-role-{{ $user->role }}">
                                        {{ $user->display_title }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="col-email text-muted">{{ $user->email }}</td>
                    <td class="col-dept text-muted">{{ $user->department->name ?? '—' }}</td>
                    <td class="col-desig text-muted">{{ $user->designation->name ?? '—' }}</td>
                    <td class="col-actions">
                        <div class="action-btn-group">
                            <a href="{{ route('users.edit', $user->uuid) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('users.destroy', $user->uuid) }}" method="POST" class="d-inline m-0">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                    onclick="return confirm('Delete admin {{ $user->name }}?')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fa-solid fa-user-shield"></i>No admin accounts found.
                            <a href="{{ route('users.create') }}">Create one</a>.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="px-4 py-3 border-top">{{ $users->links() }}</div>
    @endif
</div>
@endsection
