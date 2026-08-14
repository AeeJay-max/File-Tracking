@extends('layouts.app')
@section('title', 'Departments')

@section('breadcrumb')
<li class="breadcrumb-item active">Departments</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Departments</h1>
        <div class="page-subtitle">Manage organizational departments</div>
    </div>
    <a href="{{ route('departments.create') }}" class="btn-portal-primary"><i class="fa-solid fa-plus"></i> New Department</a>
</div>

<div class="portal-table-wrap">
    <form method="GET" class="table-toolbar">
        <input type="text" name="search" class="form-control" style="max-width:240px;"
            placeholder="Search name or code..." value="{{ request('search') }}">
        <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fa-solid fa-magnifying-glass me-1"></i>Search</button>
        <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
    </form>

    <div class="table-responsive">
        <table class="portal-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Department Name</th>
                    <th>Code</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($departments as $dept)
            <tr>
                <td class="text-muted">{{ $loop->iteration }}</td>
                <td class="fw-700">{{ $dept->name }}</td>
                <td><code>{{ $dept->code }}</code></td>
                <td>
                    @if($dept->is_active)
                    <span class="badge-status badge-active">Active</span>
                    @else
                    <span class="badge-status badge-archived">Inactive</span>
                    @endif
                </td>
                <td class="text-muted fs-sm">{{ $dept->created_at->format('d M Y') }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('departments.edit', $dept->uuid) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <form action="{{ route('departments.destroy', $dept->uuid) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Delete this department?')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-building-columns"></i>No departments found.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($departments->hasPages())
    <div class="px-4 py-3 border-top">{{ $departments->links() }}</div>
    @endif
</div>
@endsection
