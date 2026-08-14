@extends('layouts.app')
@section('title', 'File Details')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('files.index') }}">Files</a></li>
<li class="breadcrumb-item active">{{ $file->file_number }}</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $file->file_name }}</h1>
        <div class="page-subtitle">{{ $file->file_number }}</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('files.index') }}" class="btn-portal-outline">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
        @can('update', $file)
        <a href="{{ route('files.edit', $file->uuid) }}" class="btn btn-secondary">
            <i class="fa-solid fa-pencil"></i> Edit
        </a>
        @endcan
        @if($file->attachment_path)
        <a href="{{ route('files.download', $file->uuid) }}" class="btn btn-success">
            <i class="fa-solid fa-download"></i> Download
        </a>
        @endif
        @can('transfer', $file)
        <a href="{{ route('files.transfer.create', $file->uuid) }}" class="btn-portal-primary">
            <i class="fa-solid fa-right-left"></i> Transfer
        </a>
        @endcan
    </div>
</div>

{{-- FILE INFO --}}
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="portal-card h-100">
            <div class="card-header">
                <i class="fa-solid fa-circle-info me-2 text-primary"></i>File Information
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">File Name</div>
                        <div class="fw-700">{{ $file->file_name }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">File Number</div>
                        <div class="fw-700 text-portal-primary">{{ $file->file_number }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">Department</div>
                        <div>{{ $file->department->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">Status</div>
                        <div>@include('partials.status-badge', ['status' => $file->status])</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">Created By</div>
                        <div>{{ $file->creator->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">Current Holder</div>
                        <div class="d-flex align-items-center gap-2">
                            @if($file->currentHolder)
                                @if($file->currentHolder->photo_url)
                                <img src="{{ $file->currentHolder->photo_url }}"
                                     alt="{{ $file->currentHolder->name }}"
                                     style="width:24px;height:24px;border-radius:50%;object-fit:cover;"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                <div style="width:24px;height:24px;border-radius:50%;background:#e8f5e9;color:#005a2b;
                                            display:none;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;">
                                    {{ $file->currentHolder->initials }}
                                </div>
                                @else
                                <div style="width:24px;height:24px;border-radius:50%;background:#e8f5e9;color:#005a2b;
                                            display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;">
                                    {{ $file->currentHolder->initials }}
                                </div>
                                @endif
                                <span class="fw-600">{{ $file->currentHolder->name }}</span>
                            @elseif($file->status === 'pending_assignment')
                                <span class="badge-status badge-pending">
                                    <i class="fa-solid fa-hourglass-half me-1"></i>Awaiting Assignment
                                </span>
                                @if($file->currentDepartment)
                                <span class="text-muted fs-sm ms-1">by {{ $file->currentDepartment->name }}</span>
                                @endif
                            @else
                            <span class="text-muted">N/A</span>
                            @endif
                        </div>
                    </div>
                    @if($file->remarks)
                    <div class="col-12">
                        <div class="text-muted fs-sm mb-1">Remarks</div>
                        <div class="text-break" style="white-space: pre-line;">{{ $file->remarks }}</div>
                    </div>
                    @endif
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">Created At</div>
                        <div>{{ $file->created_at->format('d M Y, h:i A') }}</div>
                    </div>
                    @if($file->attachment_name)
                    <div class="col-12">
                        <div class="text-muted fs-sm mb-1">Attached Document</div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-paperclip text-muted"></i>
                            <span class="text-break">{{ $file->attachment_name }}</span>
                            @can('download', $file)
                            <a href="{{ route('files.download', $file->uuid) }}"
                               class="btn btn-sm btn-outline-success">
                                <i class="fa-solid fa-download me-1"></i>Download
                            </a>
                            @endcan
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="portal-card h-100">
            <div class="card-header">
                <i class="fa-solid fa-chart-bar me-2 text-primary"></i>Quick Stats
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted fs-sm">Total Movements</span>
                        <span class="fw-700">{{ $file->movements->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted fs-sm">Last Activity</span>
                        <span class="fw-700">
                            {{ $file->movements->last()?->created_at?->diffForHumans() ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted fs-sm">Origin Dept.</span>
                        <span class="fw-700">
                            {{ $file->movements->first()?->fromDept?->name ?? 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- LINKED-LIST TIMELINE (shared component) --}}
<div class="portal-card">
    <div class="card-header">
        <i class="fa-solid fa-route me-2 text-primary"></i>File Journey
    </div>
    <div class="card-body">
        <x-file-timeline
            :movements="$file->movements"
            :current-user-id="$file->current_user_id"
            :viewer-dept-id="auth()->user()->department_id"
            :is-super-admin="auth()->user()->role === 'super_admin'" />
    </div>
</div>
@endsection
