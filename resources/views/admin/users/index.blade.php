@extends('layouts.app')
@section('title', 'Users')

@section('breadcrumb')
<li class="breadcrumb-item active">Users</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Department Users</h1>
        <div class="page-subtitle">Manage users in your department</div>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn-portal-primary"><i class="fa-solid fa-plus"></i> Add User</a>
</div>

<div class="portal-table-wrap">
    <div class="table-responsive">
        <table class="portal-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Designation</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($users as $i => $user)
            <tr>
                <td class="text-muted">{{ $users->firstItem() + $i }}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <x-user-avatar :user="$user" :size="32" />
                        <div class="fw-700">{{ $user->name }}</div>
                    </div>
                </td>
                <td class="text-muted">{{ $user->email }}</td>
                <td>{{ $user->designation->name ?? '—' }}</td>
                <td><span class="badge-status badge-active">Active</span></td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.users.edit', $user->uuid) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        {{-- Impersonate (admin can impersonate own dept users) --}}
                        @if(!session('impersonator_id') && auth()->user()->canImpersonate($user))
                        <form method="POST" action="{{ route('impersonation.start', $user->uuid) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Impersonate {{ $user->name }}">
                                <i class="fa-solid fa-user-secret"></i>
                            </button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('admin.users.destroy', $user->uuid) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Delete this user?')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-users"></i>No users found.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="px-4 py-3 border-top">{{ $users->links() }}</div>
    @endif
</div>
@endsection
