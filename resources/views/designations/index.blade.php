@extends('layouts.app')
@section('title', 'Designations')

@section('breadcrumb')
<li class="breadcrumb-item active">Designations</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Designations</h1>
        <div class="page-subtitle">Manage job designations and titles</div>
    </div>
    <a href="{{ route('designations.create') }}" class="btn-portal-primary"><i class="fa-solid fa-plus"></i> Add Designation</a>
</div>

<div class="portal-table-wrap">
    <div class="table-responsive">
        <table class="portal-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Designation Name</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($designations as $des)
            <tr>
                <td class="text-muted">{{ $loop->iteration }}</td>
                <td class="fw-700">{{ $des->name }}</td>
                <td class="text-muted">{{ $des->department->name ?? 'N/A' }}</td>
                <td>
                    @if($des->is_active)
                    <span class="badge-status badge-active">Active</span>
                    @else
                    <span class="badge-status badge-archived">Inactive</span>
                    @endif
                </td>
                <td class="text-muted fs-sm">{{ $des->created_at->format('d M Y') }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('designations.edit', $des->uuid) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <form action="{{ route('designations.destroy', $des->uuid) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Delete this designation?')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-id-badge"></i>No designations found.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($designations->hasPages())
    <div class="px-4 py-3 border-top">{{ $designations->links() }}</div>
    @endif
</div>
@endsection
