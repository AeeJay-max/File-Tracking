@extends('layouts.app')
@section('title', 'File Details')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.files') }}">Files</a></li>
<li class="breadcrumb-item active">{{ $file->file_number }}</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $file->file_name }}</h1>
        <div class="page-subtitle">{{ $file->file_number }} &mdash; File Details &amp; Journey</div>
    </div>
    <a href="{{ route('admin.files') }}" class="btn-portal-outline">
        <i class="fa-solid fa-arrow-left"></i> Back to Files
    </a>
</div>

{{-- FILE INFO + SUMMARY --}}
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
                        <div class="text-muted fs-sm mb-1">Current Department</div>
                        <div>{{ ($file->currentDepartment ?? $file->department)?->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">Status</div>
                        <div>@include('partials.status-badge', ['status' => $file->status])</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">Created By</div>
                        <div>{{ $file->creator->name ?? ($file->currentUser->name ?? 'N/A') }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">Current Holder</div>
                        @php $holder = $file->currentHolder ?? $file->currentUser ?? null; @endphp
                        @if($holder)
                        <div class="d-flex align-items-center gap-2">
                            @if($holder->photo_url)
                            <img src="{{ $holder->photo_url }}" alt="{{ $holder->name }}"
                                 style="width:24px;height:24px;border-radius:50%;object-fit:cover;"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                            <div style="width:24px;height:24px;border-radius:50%;background:#e8f5e9;color:#005a2b;
                                        display:none;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;">
                                {{ $holder->initials }}
                            </div>
                            @else
                            <div style="width:24px;height:24px;border-radius:50%;background:#e8f5e9;color:#005a2b;
                                        display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;">
                                {{ $holder->initials }}
                            </div>
                            @endif
                            <span class="fw-600">{{ $holder->name }}</span>
                        </div>
                        @elseif($file->status === 'pending_assignment')
                        <span class="badge-status badge-pending">
                            <i class="fa-solid fa-hourglass-half me-1"></i>Awaiting Assignment
                        </span>
                        @if($file->currentDepartment ?? $file->department)
                        <div class="text-muted fs-sm mt-1">
                            <i class="fa-solid fa-building-columns fa-xs me-1"></i>
                            Held by {{ ($file->currentDepartment ?? $file->department)?->name }}
                        </div>
                        @endif
                        @else
                        <span class="text-muted">N/A</span>
                        @endif
                    </div>
                    @if($file->remarks)
                    <div class="col-12">
                        <div class="text-muted fs-sm mb-1">Remarks</div>
                        <div>{{ $file->remarks }}</div>
                    </div>
                    @endif
                    @if($file->attachment_name)
                    <div class="col-12">
                        <div class="text-muted fs-sm mb-1">Attached Document</div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-paperclip text-muted"></i>
                            <span>{{ $file->attachment_name }}</span>
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
                <i class="fa-solid fa-chart-bar me-2 text-primary"></i>Movement Summary
            </div>
            <div class="card-body">
                @php
                    $allMoves    = isset($timeline) ? $timeline : ($file->movements ?? collect());
                    $originDept  = $allMoves->sortBy('created_at')->first()?->fromDept?->name ?? 'N/A';
                    $transferred = $allMoves->where('action', 'transferred')->count();
                @endphp
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted fs-sm">Total Movements</span>
                        <span class="fw-700">{{ $allMoves->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted fs-sm">Total Transfers</span>
                        <span class="fw-700">{{ $transferred }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted fs-sm">Origin Dept.</span>
                        <span class="fw-700">{{ $originDept }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted fs-sm">Current Dept.</span>
                        <span class="fw-700">{{ ($file->currentDepartment ?? $file->department)?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted fs-sm">Current Holder</span>
                        <span class="fw-700">{{ $holder?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted fs-sm">Last Activity</span>
                        <span class="fw-700">
                            {{ $allMoves->last()?->created_at?->diffForHumans() ?? 'N/A' }}
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
        @php
            $timelineMovements = isset($timeline) ? $timeline : ($file->movements ?? collect());
        @endphp
        <x-file-timeline
            :movements="$timelineMovements"
            :current-user-id="$file->current_user_id"
            :viewer-dept-id="$viewerDeptId ?? auth()->user()->department_id"
            :is-super-admin="$isSuperAdmin ?? (auth()->user()->role === 'super_admin')" />
    </div>
</div>
@endsection
