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

@if($file->status === 'completed')
<div class="alert alert-success d-flex align-items-center justify-content-between mb-4 p-3 shadow-sm" style="border-radius:12px;background:#ecfdf5;border:1px solid #10b981;color:#065f46;">
    <div class="d-flex align-items-center gap-3">
        <div style="width:40px;height:40px;border-radius:10px;background:#10b981;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <div style="font-weight:700;font-size:1rem;">File Operations Completed &amp; Closed</div>
            <div class="small">All file handling operations were marked as completed on {{ $file->completed_at?->format('d M Y, h:i A') ?? $file->updated_at->format('d M Y, h:i A') }}. Time duration tracking is closed.</div>
        </div>
    </div>
    <span class="badge bg-success px-3 py-2 fw-700" style="font-size:.85rem;border-radius:8px;">
        <i class="fa-solid fa-lock me-1"></i>Completed / Done
    </span>
</div>
@endif

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
                    @php
                        $latestMoveRemarks = $file->movements->where('remarks', '!=', null)->where('remarks', '!=', '')->last()?->remarks;
                        $showRemarks = $file->remarks ?: $latestMoveRemarks;
                    @endphp
                    @if($showRemarks)
                    <div class="col-12">
                        <div class="text-muted fs-sm mb-1 fw-600">
                            <i class="fa-solid fa-comment-dots text-primary me-1"></i>Departmental Remarks &amp; Directives
                        </div>
                        <div class="p-3 bg-light rounded border text-break" style="white-space: pre-line; font-size:.9rem; line-height:1.5;">{{ $showRemarks }}</div>
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
                        <span class="fw-700">{{ $allMoves->sortBy('created_at')->last()?->created_at?->diffForHumans() ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Public Visibility Card --}}
        @can('togglePublicVisibility', $file)
        <div class="portal-card mt-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <i class="fa-solid fa-globe me-2 text-primary"></i>Public Visibility
                </div>
                <div>
                    @if($file->is_public)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-xs fw-700">
                        <i class="fa-solid fa-globe me-1"></i>Public
                    </span>
                    @else
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 fs-xs fw-700">
                        <i class="fa-solid fa-lock me-1"></i>Private
                    </span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted fs-sm mb-3" style="line-height:1.4;">
                    @if($file->is_public)
                        This file is currently <strong class="text-success">Publicly Searchable</strong>. Only limited, non-sensitive metadata appears in search. The document itself remains private.
                    @else
                        This file is currently <strong class="text-secondary">Private</strong> and cannot be discovered in public search.
                    @endif
                </p>

                @if($file->is_public)
                <button type="button" class="btn btn-outline-danger btn-sm w-100 fw-600" data-bs-toggle="modal" data-bs-target="#adminTogglePublicModal">
                    <i class="fa-solid fa-lock me-1"></i>Make Private
                </button>
                @else
                <button type="button" class="btn btn-outline-success btn-sm w-100 fw-600" data-bs-toggle="modal" data-bs-target="#adminTogglePublicModal">
                    <i class="fa-solid fa-globe me-1"></i>Make Public
                </button>
                @endif
            </div>
        </div>

        {{-- Confirmation Modal --}}
        <div class="modal fade" id="adminTogglePublicModal" tabindex="-1" aria-labelledby="adminTogglePublicModalLabel" aria-hidden="true" style="z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('files.togglePublic', $file->uuid) }}" method="POST" class="modal-content shadow-lg border-0" style="border-radius:16px;">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-700" id="adminTogglePublicModalLabel">
                            @if($file->is_public)
                            <i class="fa-solid fa-lock text-danger me-2"></i>Make File Private?
                            @else
                            <i class="fa-solid fa-globe text-success me-2"></i>Make File Publicly Viewable?
                            @endif
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        @if($file->is_public)
                        <p class="text-secondary mb-0" style="font-size:.92rem; line-height:1.5;">
                            Are you sure you want to make <strong>{{ $file->file_number }}</strong> private? This file will immediately be hidden from the public search page.
                        </p>
                        @else
                        <p class="text-secondary mb-2" style="font-size:.92rem; line-height:1.5;">
                            Make file <strong>{{ $file->file_number }}</strong> publicly viewable in search?
                        </p>
                        <div class="alert alert-info border-0 bg-info-subtle text-info-emphasis fs-xs mb-0" style="border-radius:10px;">
                            <i class="fa-solid fa-shield-halved me-1 fw-700"></i>
                            Only limited, non-sensitive information (File Number, Title, Origin Department, Status, Date Registered) will appear in public search. The actual file/document will <strong>NOT</strong> be publicly downloadable or accessible.
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                        @if($file->is_public)
                        <button type="submit" class="btn btn-danger btn-sm px-3 fw-600">Make Private</button>
                        @else
                        <button type="submit" class="btn btn-success btn-sm px-3 fw-600">Make Public</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
        @endcan
    </div>
</div>

{{-- MOVEMENT & TIME SPENT HISTORY TABLE --}}
<div class="portal-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-history me-2 text-primary"></i>Movement &amp; Time Spent History</span>
        <span class="badge bg-secondary">{{ $file->movements->count() }} Movements</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="portal-table" style="table-layout: fixed; min-width: 900px;">
                <colgroup>
                    <col style="width: 4%;">
                    <col style="width: 15%;">
                    <col style="width: 15%;">
                    <col style="width: 13%;">
                    <col style="width: 12%;">
                    <col style="width: 15%;">
                    <col style="width: 26%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>From Person</th>
                        <th>Sent To (Recipient)</th>
                        <th>Department</th>
                        <th>Sent At</th>
                        <th>Time Spent with Person</th>
                        <th>General Content / Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $movesList = $file->movements->sortBy('created_at')->values();
                        $lastMove = $movesList->last();
                        $isCompletedFile = ($file->status === 'completed') || ($file->completed_at !== null) || ($lastMove && $lastMove->action === 'completed');
                        $fileCompletedAt = $file->completed_at ?? ($lastMove && $lastMove->action === 'completed' ? $lastMove->created_at : null);
                    @endphp
                    @foreach($movesList as $i => $m)
                    @php
                        $startT = $m->created_at;
                        $nextM = $movesList->get($i + 1);
                        $isLast = ($i === count($movesList) - 1);
                        $endT = $nextM ? $nextM->created_at : ($isCompletedFile ? ($fileCompletedAt ?? $m->created_at) : now());
                        $durSec = $startT->diffInSeconds($endT);
                        $durText = $durSec < 60 ? 'Less than 1 min' : $startT->diffForHumans($endT, ['parts' => 2, 'short' => false, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]);
                    @endphp
                    <tr>
                        <td class="text-muted fw-600">{{ $i + 1 }}</td>
                        <td class="fw-600">
                            {{ $m->fromUser->name ?? 'System' }}
                            @if($m->fromUser?->designation)
                                <div class="text-muted fs-sm">{{ $m->fromUser->designation->name }}</div>
                            @endif
                        </td>
                        <td class="fw-700 text-portal-primary">
                            {{ $m->toUser->name ?? ($m->toDept->name ?? 'Department') }}
                            @if($m->toUser?->designation)
                                <div class="text-muted fs-sm">{{ $m->toUser->designation->name }}</div>
                            @endif
                        </td>
                        <td class="text-muted fs-sm">{{ $m->toDept->name ?? ($m->fromDept->name ?? '—') }}</td>
                        <td class="fs-sm">{{ $m->created_at->format('d M Y, h:i A') }}</td>
                        <td>
                            @if($m->action === 'completed')
                                <span class="badge bg-success py-1 px-2" style="white-space: normal; max-width: 100%; text-align: left; line-height: 1.4;">
                                    <i class="fa-solid fa-circle-check me-1"></i>
                                    Operations Completed
                                </span>
                            @else
                                <span class="badge {{ ($isLast && !$isCompletedFile) ? 'bg-success' : 'bg-warning text-dark' }} py-1 px-2" style="white-space: normal; max-width: 100%; text-align: left; line-height: 1.4;">
                                    <i class="fa-regular fa-clock me-1"></i>
                                    {{ ($isLast && !$isCompletedFile) ? 'Held so far: ' : 'Time spent: ' }}<br>
                                    {{ $durText }}
                                </span>
                            @endif
                        </td>
                        <td class="text-break fs-sm">
                            {{ $m->remarks ?: '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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
            :completed-at="$file->completed_at"
            :viewer-dept-id="$viewerDeptId ?? auth()->user()->department_id"
            :is-super-admin="$isSuperAdmin ?? (auth()->user()->role === 'super_admin')" />
    </div>
</div>
@endsection
