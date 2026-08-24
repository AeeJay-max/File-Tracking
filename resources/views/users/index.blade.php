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
            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Departmental Admin</option>
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

<div class="portal-table-wrap">
    <div class="table-responsive">
        <table class="portal-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name &amp; Role</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="text-muted">{{ $loop->iteration }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <x-user-avatar :user="$user" :size="32" />
                            <div>
                                <div class="fw-700">{{ $user->name }}</div>
                                <div class="text-muted" style="font-size:.78rem;">
                                    <span class="badge-status badge-role-{{ $user->role }}">
                                        {{ match($user->role) { 'super_admin' => 'Super Admin', 'admin' => 'Departmental Admin', default => 'User' } }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="text-muted">{{ $user->email }}</td>
                    <td class="text-muted">{{ $user->department->name ?? '—' }}</td>
                    <td class="text-muted">{{ $user->designation->name ?? '—' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('users.edit', $user->uuid) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('users.destroy', $user->uuid) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
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
