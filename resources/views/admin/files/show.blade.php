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
                    @php $movesList = $file->movements->sortBy('created_at')->values(); @endphp
                    @foreach($movesList as $i => $m)
                    @php
                        $startT = $m->created_at;
                        $nextM = $movesList->get($i + 1);
                        $endT = $nextM ? $nextM->created_at : now();
                        $durSec = $startT->diffInSeconds($endT);
                        $durText = $durSec < 60 ? 'Less than 1 min' : $startT->diffForHumans($endT, ['parts' => 2, 'short' => false, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]);
                        $isLast = ($i === count($movesList) - 1);
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
                            <span class="badge {{ $isLast ? 'bg-success' : 'bg-warning text-dark' }} py-1 px-2" style="white-space: normal; max-width: 100%; text-align: left; line-height: 1.4;">
                                <i class="fa-regular fa-clock me-1"></i>
                                {{ $isLast ? 'Held so far: ' : 'Time spent: ' }}<br>
                                {{ $durText }}
                            </span>
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
