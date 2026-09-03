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
    @php
        $u = auth()->user();
        $isPermSec = ($u->designation?->name === 'Permanent Secretary' || $u->email === 'permsec@filetrack.local');
        $isRecordsDept = ($u->department?->code === 'REC' || Str::contains(Str::lower($u->department?->name ?? ''), 'record'));
        $isHolder = ((int) $file->current_user_id === $u->id);
        $isDeptAdmin = ($u->role === 'admin' && (int) $file->current_department_id === (int) $u->department_id);
        $isOfficer = ($u->role === 'user' && ! $isPermSec && $isHolder);
        $allDepts = \App\Models\Department::where('is_active', true)->orderBy('name')->get();
    @endphp

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

        @if($isRecordsDept && $file->status !== 'completed')
            <button type="button" class="btn btn-success fw-700 shadow-sm ms-1" data-bs-toggle="modal" data-bs-target="#completeOperationsModal">
                <i class="fa-solid fa-circle-check me-1"></i>Mark Operations as Done
            </button>
        @endif

        @if($file->status !== 'completed')
        @if($isPermSec && $isHolder)
            {{-- Permanent Secretary: "Mark as Done & Return to Records" --}}
            <button type="button" class="btn-portal-primary" data-bs-toggle="modal" data-bs-target="#permsecDoneModal">
                <i class="fa-solid fa-circle-check me-1"></i>Mark as Done (Send to Records)
            </button>
        @elseif($isOfficer)
            {{-- Department Officer: "Mark as Done & Send to Dept Admin" --}}
            <button type="button" class="btn-portal-primary" data-bs-toggle="modal" data-bs-target="#officerDoneModal">
                <i class="fa-solid fa-circle-check me-1"></i>Mark as Done (Send to Admin)
            </button>
        @elseif($isDeptAdmin && ! $isRecordsDept)
            {{-- Handling Dept Admin: "Send Back to Records" + "Assign Officer" --}}
            <button type="button" class="btn-portal-outline" data-bs-toggle="modal" data-bs-target="#adminReturnRecordsModal">
                <i class="fa-solid fa-reply me-1"></i>Send Back to Records
            </button>
            @can('transfer', $file)
            <a href="{{ route('files.transfer.create', $file->uuid) }}" class="btn-portal-primary">
                <i class="fa-solid fa-user-plus me-1"></i>Assign Officer
            </a>
            @endcan
        @elseif($isRecordsDept && $file->recommendedDepartment)
            {{-- Records Admin: Send to Recommended Department --}}
            <form action="{{ route('files.dispatchRecommended', $file->uuid) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn-portal-primary">
                    <i class="fa-solid fa-paper-plane me-1"></i>Send to {{ $file->recommendedDepartment->name }}
                </button>
            </form>
            @can('transfer', $file)
            <a href="{{ route('files.transfer.create', $file->uuid) }}" class="btn-portal-outline">
                <i class="fa-solid fa-right-left me-1"></i>Manual Transfer
            </a>
            @endcan
        @elseif(! $isPermSec)
            @can('transfer', $file)
            <a href="{{ route('files.transfer.create', $file->uuid) }}" class="btn-portal-primary">
                <i class="fa-solid fa-right-left me-1"></i>Transfer / Dispatch
            </a>
            @endcan
        @endif
        @endif
    </div>
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

@if($file->recommendedDepartment && $isRecordsDept)
<div class="alert alert-warning d-flex align-items-center justify-content-between mb-4 p-3" style="border-radius:12px;background:rgba(217,119,6,.1);border:1px solid rgba(217,119,6,.3);color:#92400e;">
    <div class="d-flex align-items-center gap-3">
        <div style="width:40px;height:40px;border-radius:10px;background:#fef3c7;color:#d97706;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">
            <i class="fa-solid fa-flag"></i>
        </div>
        <div>
            <div style="font-weight:700;font-size:.95rem;">Flagged Target Department: {{ $file->recommendedDepartment->name }} ({{ $file->recommendedDepartment->code }})</div>
            <div class="small text-muted">Recommended for dispatch by {{ $file->movements->last()?->fromUser?->name ?? 'Executive' }}. Click button to dispatch directly to department admin.</div>
        </div>
    </div>
    <form action="{{ route('files.dispatchRecommended', $file->uuid) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-warning fw-700 shadow-sm">
            <i class="fa-solid fa-paper-plane me-1"></i>Send to {{ $file->recommendedDepartment->name }}
        </button>
    </form>
</div>
@endif

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
        <x-file-timeline
            :movements="$file->movements"
            :current-user-id="$file->current_user_id"
            :completed-at="$file->completed_at"
            :viewer-dept-id="auth()->user()->department_id"
            :is-super-admin="auth()->user()->role === 'super_admin'" />
    </div>
</div>
@endsection

@section('modals')
@if($isPermSec && $isHolder)
{{-- Modal: Permanent Secretary Done & Send to Records --}}
<div class="modal fade" id="permsecDoneModal" tabindex="-1" aria-labelledby="permsecDoneModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('files.permsecDone', $file->uuid) }}" method="POST" class="modal-content shadow-lg border-0" style="border-radius:16px; background:#ffffff; overflow:visible;">
            @csrf
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-700" id="permsecDoneModalLabel">
                    <i class="fa-solid fa-circle-check text-success me-2"></i>Complete Review &amp; Send to Records
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted fs-sm mb-3">
                    Select the recommended handling department for this file and enter any directives. The file will be transferred back to <strong>Records Admin</strong> for dispatch.
                </p>
                <div class="mb-3">
                    <label class="form-label fw-600" for="permsec_dept">Recommended Handling Department</label>
                    <select name="recommended_department_id" id="permsec_dept" class="form-select">
                        <option value="">-- Select Target Department --</option>
                        @foreach($allDepts as $d)
                        <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600" for="permsec_remarks">Executive Directives / Remarks</label>
                    <textarea name="remarks" id="permsec_remarks" rows="3" class="form-control" placeholder="Enter instructions for Records and target department..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-top bg-light" style="border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-portal-primary"><i class="fa-solid fa-paper-plane me-1"></i>Done &amp; Send to Records</button>
            </div>
        </form>
    </div>
</div>
@endif

@if($isOfficer)
{{-- Modal: Department Officer Done & Send to Dept Admin --}}
<div class="modal fade" id="officerDoneModal" tabindex="-1" aria-labelledby="officerDoneModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('files.officerDone', $file->uuid) }}" method="POST" class="modal-content shadow-lg border-0" style="border-radius:16px; background:#ffffff; overflow:visible;">
            @csrf
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-700" id="officerDoneModalLabel">
                    <i class="fa-solid fa-circle-check text-success me-2"></i>Mark Task as Completed
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted fs-sm mb-3">
                    Mark your work on this file as completed. The file will be sent back to your <strong>Department Admin</strong>.
                </p>
                <div class="mb-3">
                    <label class="form-label fw-600" for="officer_dept">Suggested Next Department (Optional)</label>
                    <select name="recommended_department_id" id="officer_dept" class="form-select">
                        <option value="">-- Select Suggested Department --</option>
                        @foreach($allDepts as $d)
                        <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600" for="officer_remarks">Completion Notes / Action Summary</label>
                    <textarea name="remarks" id="officer_remarks" rows="3" class="form-control" placeholder="Summarize the action taken on this file..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-top bg-light" style="border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-portal-primary"><i class="fa-solid fa-paper-plane me-1"></i>Mark Done &amp; Send to Admin</button>
            </div>
        </form>
    </div>
</div>
@endif

@if($isDeptAdmin && ! $isRecordsDept)
{{-- Modal: Department Admin Return to Records --}}
<div class="modal fade" id="adminReturnRecordsModal" tabindex="-1" aria-labelledby="adminReturnRecordsModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('files.adminReturnRecords', $file->uuid) }}" method="POST" class="modal-content shadow-lg border-0" style="border-radius:16px; background:#ffffff; overflow:visible;">
            @csrf
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-700" id="adminReturnRecordsModalLabel">
                    <i class="fa-solid fa-reply text-primary me-2"></i>Send Back to Records Department
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted fs-sm mb-3">
                    Return this file to <strong>Records Admin</strong> so it can be routed to the next designated department or archived.
                </p>
                <div class="mb-3">
                    <label class="form-label fw-600" for="admin_dept">Recommended Next Department (Optional)</label>
                    <select name="recommended_department_id" id="admin_dept" class="form-select">
                        <option value="">-- Select Next Recommended Department --</option>
                        @foreach($allDepts as $d)
                        <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600" for="admin_remarks">Departmental Return Remarks</label>
                    <textarea name="remarks" id="admin_remarks" rows="3" class="form-control" placeholder="Enter notes for Records..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-top bg-light" style="border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-portal-primary"><i class="fa-solid fa-paper-plane me-1"></i>Send Back to Records</button>
            </div>
        </form>
    </div>
</div>
@endif


@if($isRecordsDept && $file->status !== 'completed')
{{-- Modal: Records Mark Operations as Completed --}}
<div class="modal fade" id="completeOperationsModal" tabindex="-1" aria-labelledby="completeOperationsModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('files.completeOperations', $file->uuid) }}" method="POST" class="modal-content shadow-lg border-0" style="border-radius:16px; background:#ffffff; overflow:visible;">
            @csrf
            <div class="modal-header border-bottom bg-success text-white" style="border-top-left-radius:16px; border-top-right-radius:16px;">
                <h5 class="modal-title fw-700 text-white" id="completeOperationsModalLabel">
                    <i class="fa-solid fa-circle-check me-2"></i>Mark File Operations as Done
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-secondary fw-600 mb-3">
                    Are you sure all handling and departmental processing operations for this file are finished?
                </p>
                <div class="alert alert-success bg-opacity-10 border-success text-success p-3 rounded mb-3">
                    <i class="fa-solid fa-clock me-1"></i>
                    <strong>Notice:</strong> Marking this file as completed will set its status to <strong>Completed / Done</strong> and permanently stop the duration counter.
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600" for="complete_remarks">Completion Remarks / Summary</label>
                    <textarea name="remarks" id="complete_remarks" rows="3" class="form-control" placeholder="Enter final summary or archive notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-top bg-light" style="border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success fw-700 px-4"><i class="fa-solid fa-check me-1"></i>Confirm &amp; Close File</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
