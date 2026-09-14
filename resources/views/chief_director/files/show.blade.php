@extends('layouts.app')

@section('title', 'Assigned File — ' . $file->file_number)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('chief_director.dashboard') }}">Chief Director</a></li>
<li class="breadcrumb-item active">{{ $file->file_number }}</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $file->file_name }}</h1>
        <div class="page-subtitle">{{ $file->file_number }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('chief_director.dashboard') }}" class="btn-portal-outline">
            <i class="fa-solid fa-arrow-left me-1"></i>Back to Dashboard
        </a>
        @if($activeAssignment)
        <button type="button" class="btn-portal-primary" data-bs-toggle="modal" data-bs-target="#completeActionModal">
            <i class="fa-solid fa-check-double me-1"></i>Complete Action &amp; Return File
        </button>
        @endif
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4 p-3 shadow-sm" style="border-radius:12px;background:#ecfdf5;border:1px solid #10b981;color:#065f46;">
    <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4 p-3 shadow-sm" style="border-radius:12px;background:#fef2f2;border:1px solid #ef4444;color:#991b1b;">
    <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- PROMINENT SECTION: PERMANENT SECRETARY'S INSTRUCTIONS --}}
@if($activeAssignment)
<div class="portal-card mb-4" style="border-left: 6px solid #005a2b; background: #f4fbf7;">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #c8e6c9;">
        <span class="fs-5 fw-800 text-portal-primary">
            <i class="fa-solid fa-user-shield me-2 text-success"></i>PERMANENT SECRETARY'S INSTRUCTIONS
        </span>
        <span class="badge bg-success px-3 py-2 fw-700">
            <i class="fa-solid fa-shield-halved me-1"></i>Acting PermSec Authority: ACTIVE
        </span>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="text-muted fs-sm fw-600 mb-1">Assigned By</div>
                <div class="fw-700 text-dark">{{ $activeAssignment->assignedBy->name ?? 'Permanent Secretary' }} (Permanent Secretary)</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted fs-sm fw-600 mb-1">Delegated Authority</div>
                <div><span class="badge bg-primary p-2">Acting Permanent Secretary</span></div>
            </div>
            <div class="col-12">
                <div class="text-muted fs-sm fw-600 mb-1">Instructions</div>
                <div class="p-3 bg-white rounded border text-break shadow-sm" style="white-space: pre-line; font-size: 0.95rem; line-height: 1.6; border-color: #a5d6a7 !important;">
                    {{ $activeAssignment->instructions }}
                </div>
            </div>

            @if($activeAssignment->required_action)
            <div class="col-md-6">
                <div class="text-muted fs-sm fw-600 mb-1">Required Action</div>
                <div class="p-3 bg-white rounded border text-break shadow-sm" style="font-size: 0.9rem;">
                    {{ $activeAssignment->required_action }}
                </div>
            </div>
            @endif
            @if($activeAssignment->next_step)
            <div class="col-md-6">
                <div class="text-muted fs-sm fw-600 mb-1">Next Step Directives</div>
                <div class="p-3 bg-white rounded border text-break shadow-sm" style="font-size: 0.9rem;">
                    {{ $activeAssignment->next_step }}
                </div>
            </div>
            @endif
            <div class="col-md-6">
                <div class="text-muted fs-sm fw-600 mb-1">Return Destination Upon Completion</div>
                <div>
                    <span class="badge {{ $activeAssignment->return_destination === 'permsec_office' ? 'bg-primary' : 'bg-secondary' }} p-2" style="font-size:0.9rem;">
                        <i class="fa-solid {{ $activeAssignment->return_destination === 'permsec_office' ? 'fa-building-user' : 'fa-folder' }} me-1"></i>
                        {{ $activeAssignment->return_destination === 'permsec_office' ? "Permanent Secretary's Office" : "Records Department" }}
                    </span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-muted fs-sm fw-600 mb-1">Delegated Date</div>
                <div class="fw-600">{{ $activeAssignment->assigned_at->format('d M Y, h:i A') }}</div>
            </div>
        </div>

        <div class="mt-4 pt-3 border-top d-flex justify-content-end">
            <button type="button" class="btn btn-primary fw-700 px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#completeActionModal">
                <i class="fa-solid fa-check-double me-1"></i>Action File &amp; Return Now
            </button>
        </div>
    </div>
</div>
@else
<div class="alert alert-info d-flex align-items-center gap-3 p-3 mb-4 shadow-sm" style="border-radius:12px;">
    <i class="fa-solid fa-circle-info fa-2x text-info"></i>
    <div>
        <div class="fw-700">Standard View Mode</div>
        <div class="small">No active Acting Permanent Secretary delegation exists for this file. Acting Permanent Secretary actions are restricted until delegated by the Permanent Secretary.</div>
    </div>
</div>
@endif

{{-- FILE INFORMATION CARD --}}
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
                        <div class="text-muted fs-sm mb-1">Origin Department</div>
                        <div>{{ $file->department->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">Current Department</div>
                        <div class="fw-600">{{ $file->currentDepartment->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">Current Holder</div>
                        <div class="fw-600 text-portal-primary">{{ $file->currentHolder->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">Status</div>
                        <div>@include('partials.status-badge', ['status' => $file->status])</div>
                    </div>
                    @if($file->attachment_name)
                    <div class="col-12">
                        <div class="text-muted fs-sm mb-1">Attachment</div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-paperclip text-muted"></i>
                            <span>{{ $file->attachment_name }}</span>
                            <a href="{{ route('files.download', $file->uuid) }}" class="btn btn-sm btn-outline-success">
                                <i class="fa-solid fa-download me-1"></i>Download
                            </a>
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
                <i class="fa-solid fa-chart-bar me-2 text-primary"></i>Movement Stats
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted fs-sm">Total Movements</span>
                        <span class="fw-700">{{ $file->movements->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted fs-sm">Last Activity</span>
                        <span class="fw-700">{{ $file->movements->last()?->created_at?->diffForHumans() ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MOVEMENT HISTORY TABLE --}}
<div class="portal-card mb-4">
    <div class="card-header">
        <i class="fa-solid fa-history me-2 text-primary"></i>Movement Timeline
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>From</th>
                        <th>To (Recipient)</th>
                        <th>Action</th>
                        <th>Sent At</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($file->movements->sortBy('created_at')->values() as $i => $m)
                    <tr>
                        <td class="text-muted fw-600">{{ $i + 1 }}</td>
                        <td>{{ $m->fromUser->name ?? 'System' }}</td>
                        <td class="fw-600 text-portal-primary">{{ $m->toUser->name ?? ($m->toDept->name ?? 'Department') }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $m->action }}</span></td>
                        <td class="fs-sm">{{ $m->created_at->format('d M Y, h:i A') }}</td>
                        <td class="fs-sm text-break">{{ $m->remarks ?: '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($activeAssignment)
{{-- MODAL: CHIEF DIRECTOR COMPLETE ACTION --}}
<div class="modal fade" id="completeActionModal" tabindex="-1" aria-labelledby="completeActionModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('chief_director.files.completeAction', $file->uuid) }}" method="POST" class="modal-content shadow-lg border-0" style="border-radius:16px; background:#ffffff;">
            @csrf
            <div class="modal-header border-bottom bg-primary text-white" style="border-top-left-radius:16px; border-top-right-radius:16px;">
                <h5 class="modal-title fw-700 text-white" id="completeActionModalLabel">
                    <i class="fa-solid fa-shield-halved me-2"></i>Complete Delegated Action (Acting PermSec)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info bg-opacity-10 border-info text-dark p-3 rounded mb-3">
                    <i class="fa-solid fa-info-circle me-1 text-info"></i>
                    You are completing the action requested by the <strong>Permanent Secretary</strong>. Your action details will be recorded in the official audit trail.
                </div>

                <div class="mb-3">
                    <label for="action_taken" class="form-label fw-600">Action Taken <span class="text-danger">*</span></label>
                    <textarea name="action_taken" id="action_taken" rows="4" class="form-control" placeholder="Detail the exact executive action taken on this file..." required></textarea>
                </div>

                <div class="mb-3">
                    <label for="next_action" class="form-label fw-600">Next Recommended Action / Follow-up (Optional)</label>
                    <textarea name="next_action" id="next_action" rows="2" class="form-control" placeholder="Enter any follow-up recommendations for the receiving office..."></textarea>
                </div>

                <div class="mb-3">
                    <label for="return_to" class="form-label fw-600">Return Destination <span class="text-danger">*</span></label>
                    <select name="return_to" id="return_to" class="form-select" required>
                        <option value="permsec_office" {{ $activeAssignment->return_destination === 'permsec_office' ? 'selected' : 'disabled' }}>
                            Permanent Secretary's Office {{ $activeAssignment->return_destination === 'records' ? '(Disabled by PermSec)' : '' }}
                        </option>
                        <option value="records" {{ $activeAssignment->return_destination === 'records' ? 'selected' : 'disabled' }}>
                            Records Department {{ $activeAssignment->return_destination === 'permsec_office' ? '(Disabled — Permanent Secretary directed return to PermSec Office)' : '' }}
                        </option>
                    </select>
                    <small class="text-muted mt-1 d-block">
                        <i class="fa-solid fa-lock me-1 text-primary"></i>Return destination is locked per Permanent Secretary directive.
                    </small>
                </div>

            </div>
            <div class="modal-footer border-top bg-light" style="border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-portal-primary px-4"><i class="fa-solid fa-paper-plane me-1"></i>Submit &amp; Return File</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
