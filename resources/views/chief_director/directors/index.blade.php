@extends('layouts.app')

@section('title', 'Departmental Directors Management')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('chief_director.dashboard') }}">Chief Director</a></li>
<li class="breadcrumb-item active">Directors</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Departmental Directors</h1>
        <div class="page-subtitle">Manage and monitor departmental Director accounts</div>
    </div>
    <div>
        <a href="{{ route('chief_director.dashboard') }}" class="btn-portal-outline">
            <i class="fa-solid fa-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4 p-3 shadow-sm" style="border-radius:12px;background:#ecfdf5;border:1px solid #10b981;color:#065f46;">
    <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- FILTERS --}}
<div class="portal-card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('chief_director.directors.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by Director name, email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="department_id" class="form-select">
                    <option value="">-- All Departments --</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }} ({{ $dept->code }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-portal-primary w-100"><i class="fa-solid fa-filter me-1"></i>Filter</button>
                @if(request()->hasAny(['search', 'department_id']))
                <a href="{{ route('chief_director.directors.index') }}" class="btn btn-light"><i class="fa-solid fa-xmark"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- DIRECTORS TABLE --}}
<div class="portal-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-users me-2 text-primary"></i>Departmental Directors</span>
        <span class="badge bg-secondary">{{ $directors->total() }} Directors Found</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th style="width: 50px; min-width: 50px; max-width: 50px;" class="text-center">#</th>
                        <th>Director Name</th>
                        <th>Display Title</th>
                        <th>Department</th>
                        <th>Contact Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($directors as $i => $dir)
                    <tr>
                        <td class="text-muted fw-600 text-center" style="width: 50px; min-width: 50px; max-width: 50px;">{{ $directors->firstItem() + $i }}</td>

                        <td class="fw-700">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:36px;height:36px;border-radius:50%;background:#e8f5e9;color:#005a2b;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;">
                                    {{ $dir->initials }}
                                </div>
                                <div>
                                    <a href="{{ route('chief_director.directors.show', $dir->uuid) }}" class="text-dark fw-700">{{ $dir->name }}</a>
                                </div>
                            </div>
                        </td>
                        <td class="fw-600 text-portal-primary">
                            <i class="fa-solid fa-user-tie me-1"></i>{{ $dir->display_title }}
                        </td>

                        <td>
                            <span class="badge bg-light text-dark border">{{ $dir->department->name ?? 'Unassigned' }}</span>
                        </td>
                        <td class="fs-sm text-muted">{{ $dir->email }}</td>
                        <td class="fs-sm">{{ $dir->phone ?? 'N/A' }}</td>
                        <td>
                            @if($dir->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('chief_director.directors.show', $dir->uuid) }}" class="btn btn-sm btn-outline-primary" title="View Profile">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('chief_director.directors.edit', $dir->uuid) }}" class="btn btn-sm btn-outline-secondary" title="Edit Director">
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
                        <td colspan="8" class="text-center py-5 text-muted">No departmental Directors found matching your search.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($directors->hasPages())
    <div class="card-footer">
        {{ $directors->links() }}
    </div>
    @endif
</div>
@endsection
