@extends('layouts.app')
@section('title', 'Folders')

@section('breadcrumb')
<li class="breadcrumb-item active">Folders</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Records &amp; Department Folders</h1>
        <div class="page-subtitle">Organize and manage official document folders</div>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addFolderPageModal">
            <i class="fa-solid fa-folder-plus me-1"></i>Add New Folder
        </button>
        @can('create', App\Models\FileRecord::class)
        <button type="button" class="btn-portal-primary" data-bs-toggle="modal" data-bs-target="#createFileModal">
            <i class="fa-solid fa-plus me-1"></i>New File
        </button>
        @endcan
    </div>
</div>

<div class="portal-table-wrap">
    <form action="{{ route('folders.index') }}" method="GET" class="table-toolbar">
        <input type="text" name="search" class="form-control" style="max-width:280px;"
               placeholder="Search folder number or name…"
               value="{{ request('search') }}">
        <button type="submit" class="btn btn-primary btn-sm px-3">
            <i class="fa-solid fa-magnifying-glass me-1"></i>Search
        </button>
        <a href="{{ route('folders.index') }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
    </form>

    <div class="row g-3 p-3">
        @forelse($folders as $folder)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0" style="border-radius:12px;background:#ffffff;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-700 px-2 py-1" style="border-radius:6px;font-size:.78rem;">
                                <i class="fa-solid fa-folder me-1"></i>{{ $folder->folder_number }}
                            </span>
                            <span class="badge bg-secondary bg-opacity-10 text-dark fw-600 fs-xs" style="border-radius:12px;">
                                {{ $folder->files_count }} {{ Str::plural('File', $folder->files_count) }}
                            </span>
                        </div>
                        <h5 class="fw-700 text-dark mb-1" style="font-size:1.05rem;">{{ $folder->folder_name }}</h5>
                        <p class="text-muted fs-xs mb-3">
                            <i class="fa-solid fa-building-columns me-1"></i>{{ $folder->department->name ?? 'Global / Records' }}
                        </p>
                    </div>
                    <div class="pt-2 border-top d-flex align-items-center justify-content-between">
                        <span class="text-muted fs-xs">
                            <i class="fa-solid fa-user me-1"></i>{{ $folder->creator->name ?? 'System' }}
                        </span>
                        <a href="{{ route('files.index', ['folder_id' => $folder->id]) }}" class="btn btn-sm btn-outline-primary" style="border-radius:6px;font-size:.8rem;">
                            View Files <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 py-4 text-center text-muted">
            <i class="fa-solid fa-folder-open fa-3x mb-2 text-secondary opacity-50"></i>
            <p class="mb-0">No folders found.</p>
        </div>
        @endforelse
    </div>

    @if($folders->hasPages())
    <div class="px-3 py-2 border-top">
        {{ $folders->links() }}
    </div>
    @endif
</div>

{{-- Add Folder Modal --}}
<div class="modal fade" id="addFolderPageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 12px 40px rgba(15,23,42,.18);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800">
                    <i class="fa-solid fa-folder-plus me-2 text-primary"></i>Add New Folder
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('folders.store') }}" method="POST">
                @csrf
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-600">Folder Number <span class="text-danger">*</span></label>
                        <input type="text" name="folder_number" class="form-control" placeholder="e.g. FOLD-2026-001" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Folder Name <span class="text-danger">*</span></label>
                        <input type="text" name="folder_name" class="form-control" placeholder="e.g. Finance &amp; Budget Reports 2026" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-600">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Save Folder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
