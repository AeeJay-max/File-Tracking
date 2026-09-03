@extends('layouts.app')
@section('title', 'Send / Transfer Document')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('files.index') }}">Files</a></li>
<li class="breadcrumb-item active">Send Document</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Send / Transfer Document</h1>
        <div class="page-subtitle">Send document contents directly to a specific person or department</div>
    </div>
    <a href="{{ route('files.index') }}" class="btn-portal-outline">
        <i class="fa-solid fa-arrow-left"></i> Back
    </a>
</div>

<div class="alert alert-info d-flex align-items-start gap-2 mb-3" style="background:rgba(15,118,110,.08);border:1px solid rgba(15,118,110,.25);color:#0f766e;border-radius:10px;">
    <i class="fa-solid fa-route fa-lg mt-1"></i>
    <div>
        <strong style="font-size:.9rem;">Standard Ministry File Transfer Sequence:</strong>
        <div class="d-flex flex-wrap align-items-center gap-1 mt-1 fs-sm fw-600" style="color:#0f172a;">
            <span class="badge bg-light text-dark border"><i class="fa-solid fa-file-circle-plus me-1 text-success"></i>1. Records Creator</span> &rarr;
            <span class="badge bg-light text-dark border"><i class="fa-solid fa-user-gear me-1 text-primary"></i>2. Records Admin</span> &rarr;
            <span class="badge bg-light text-dark border"><i class="fa-solid fa-user-tie me-1 text-warning"></i>3. Permanent Secretary</span> &rarr;
            <span class="badge bg-light text-dark border"><i class="fa-solid fa-user-gear me-1 text-primary"></i>4. Records Admin</span> &rarr;
            <span class="badge bg-light text-dark border"><i class="fa-solid fa-building-columns me-1 text-info"></i>5. Handling Department</span>
        </div>
    </div>
</div>

@if(! $file->hasBeenToPermSec())
<div class="alert alert-warning d-flex align-items-center gap-2 mb-3" style="border-radius:10px;">
    <i class="fa-solid fa-triangle-exclamation fa-lg text-warning"></i>
    <div>
        <strong>Permanent Secretary Review Required:</strong> This file has not yet been reviewed by the Permanent Secretary. In accordance with ministry policy, it must be sent to the Permanent Secretary before it can be dispatched to any other department.
    </div>
</div>
@endif

<div class="row g-3 justify-content-center">

    {{-- Document Summary --}}
    <div class="col-md-4 col-lg-3">
        <div class="portal-card">
            <div class="card-header">
                <i class="fa-solid fa-file-lines me-2 text-primary"></i>Document Overview
            </div>
            <div class="card-body">
                <dl class="mb-0" style="display:grid;grid-template-columns:auto 1fr;gap:8px 12px;font-size:.85rem;">
                    <dt class="text-muted fw-600">Document No.</dt>
                    <dd class="fw-700 text-portal-primary mb-0">{{ $file->file_number }}</dd>

                    <dt class="text-muted fw-600">Title / Subject</dt>
                    <dd class="fw-600 mb-0">{{ $file->file_name }}</dd>

                    <dt class="text-muted fw-600">Origin Dept.</dt>
                    <dd class="mb-0">{{ $file->department->name ?? 'N/A' }}</dd>

                    <dt class="text-muted fw-600">Current Sender</dt>
                    <dd class="mb-0">{{ auth()->user()->name }}</dd>

                    <dt class="text-muted fw-600">Status</dt>
                    <dd class="mb-0">@include('partials.status-badge', ['status' => $file->status])</dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Transfer Form --}}
    <div class="col-md-8 col-lg-6">
        <div class="portal-card">
            <div class="card-header">
                <i class="fa-solid fa-paper-plane me-2 text-primary"></i>Recipient &amp; Content Details
            </div>
            <div class="card-body">

                <form action="{{ route('files.transfer.store') }}"
                      method="POST"
                      id="transferForm"
                      novalidate>
                    @csrf

                    <input type="hidden" name="file_record_uuid" value="{{ $file->uuid }}">
                    <input type="hidden" name="destination_type" id="destination_type" value="{{ old('destination_type', 'user') }}">
                    <input type="hidden" name="to_user_id"       id="to_user_id"       value="{{ old('to_user_id') }}">
                    <input type="hidden" name="department_id"    id="department_id"    value="{{ old('department_id') }}">

                    @php
                        $needsPermSec  = ! $file->hasBeenToPermSec();
                        $authUser      = auth()->user();
                        $isRecordsUser = ($authUser->department?->code === 'REC'
                            || \Illuminate\Support\Str::contains(
                                \Illuminate\Support\Str::lower($authUser->department?->name ?? ''), 'record'));

                        $lastMove = $file->movements->last();
                        $lastSender = $lastMove?->fromUser;
                        $isLastSenderPermSec = $lastSender && (
                            $lastSender->designation?->name === 'Permanent Secretary' ||
                            $lastSender->email === 'permsec@filetrack.local'
                        );

                        // Lock destination ONLY when PermSec was the immediate sender who assigned a recommended dept AND current user is from Records
                        $permsecLockedDept = ($isLastSenderPermSec && $file->recommended_department_id && $isRecordsUser)
                            ? $file->recommendedDepartment
                            : null;
                    @endphp

                    {{-- ════════════════════════════════════════════════════════
                         LOCKED DESTINATION — shown only when PermSec has already
                         assigned a department and the current user is from Records
                    ══════════════════════════════════════════════════════════ --}}
                    @if($permsecLockedDept)

                        {{-- Force department mode with the PermSec-assigned dept --}}
                        <script>document.getElementById('destination_type').value='department';</script>
                        <input type="hidden" name="destination_type" value="department">
                        <input type="hidden" name="department_id"    value="{{ $permsecLockedDept->id }}">

                        <div class="alert d-flex align-items-start gap-3 mb-3"
                             style="background:rgba(234,179,8,.08);border:1px solid rgba(234,179,8,.35);border-radius:10px;color:#713f12;">
                            <i class="fa-solid fa-lock fa-lg mt-1" style="color:#ca8a04;"></i>
                            <div>
                                <div class="fw-700 mb-1" style="font-size:.9rem;">
                                    Destination Locked by Permanent Secretary
                                </div>
                                <div class="text-muted" style="font-size:.82rem;">
                                    The Permanent Secretary has directed this file to a specific department.
                                    Records cannot change the destination.
                                </div>
                            </div>
                        </div>

                        {{-- Read-only department display --}}
                        <div class="mb-4">
                            <label class="form-label fw-600">Destination Department</label>
                            <div class="d-flex align-items-center gap-3 px-3 py-3"
                                 style="background:#f0fdf4;border:2px solid #16a34a;border-radius:10px;">
                                <i class="fa-solid fa-building-columns fa-lg text-success"></i>
                                <div>
                                    <div class="fw-700" style="font-size:1rem;">{{ $permsecLockedDept->name }}</div>
                                    @if($permsecLockedDept->code)
                                    <div class="text-muted" style="font-size:.8rem;">Code: {{ $permsecLockedDept->code }}</div>
                                    @endif
                                </div>
                                <span class="ms-auto badge text-white fw-600 px-2 py-1"
                                      style="background:#16a34a;font-size:.75rem;border-radius:6px;">
                                    <i class="fa-solid fa-circle-check me-1"></i>PermSec Assigned
                                </span>
                            </div>
                        </div>

                    @else

                    {{-- ── Mode Switcher ────────────────────────────────────── --}}
                    <div class="mb-3">
                        <label class="form-label fw-600">Send Target <span class="text-danger">*</span></label>
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-primary active fw-600" id="btnModePerson">
                                <i class="fa-solid fa-user me-1"></i> Send Directly to Person
                            </button>
                            <button type="button" class="btn btn-outline-primary fw-600 {{ $needsPermSec ? 'disabled opacity-50' : '' }}" id="btnModeDept" {{ $needsPermSec ? 'disabled title="Locked: Must be sent to Permanent Secretary first"' : '' }}>
                                <i class="fa-solid fa-{{ $needsPermSec ? 'lock' : 'building-columns' }} me-1"></i> Send to Department {{ $needsPermSec ? '(Locked)' : '' }}
                            </button>
                        </div>
                    </div>

                    {{-- ── SECTION 1: Select Specific Person ────────────────── --}}
                    <div class="mb-3" id="personSection">
                        <label for="personSelect" class="form-label fw-600">
                            Select Recipient Person <span class="text-danger">*</span>
                        </label>

                        <div class="position-relative mb-2">
                            <input type="text"
                                   id="userSearchInput"
                                   class="form-control mb-2"
                                   placeholder="{{ $needsPermSec ? 'Select Permanent Secretary below (Other recipients locked)…' : 'Search person by name, department, or designation…' }}"
                                   autocomplete="off">

                            {{-- AJAX Person Search Results --}}
                            <div id="userSearchResults"
                                 class="list-group shadow"
                                 style="display:none;position:absolute;z-index:1055;width:100%;top:calc(100% + 2px);border-radius:8px;overflow:hidden;">
                            </div>
                        </div>

                        <select id="personSelect"
                                class="form-select @error('to_user_id') is-invalid @enderror">
                            <option value="" disabled {{ $needsPermSec ? '' : 'selected' }}>— Choose recipient person —</option>

                            @if($sameDeptUsers->count())
                            <optgroup label="Your Department ({{ auth()->user()->department->name ?? 'Department' }})">
                                @foreach($sameDeptUsers as $u)
                                <option value="{{ $u->id }}" {{ old('to_user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} {{ $u->designation && $u->designation->name !== '—' ? ' (' . $u->designation->name . ')' : '' }}
                                </option>
                                @endforeach
                            </optgroup>
                            @endif

                            @php
                                $otherUsers = $allUsers->reject(fn($u) => $u->department_id === auth()->user()->department_id);
                                $groupedOthers = $otherUsers->groupBy(fn($u) => $u->department->name ?? 'Other Departments');
                            @endphp

                            @foreach($groupedOthers as $deptName => $users)
                            <optgroup label="{{ $deptName }}">
                                @foreach($users as $u)
                                @php $isPS = ($u->designation?->name === 'Permanent Secretary' || $u->email === 'permsec@filetrack.local'); @endphp
                                <option value="{{ $u->id }}" {{ $needsPermSec && !$isPS ? 'disabled style=color:#94a3b8;background:#f8fafc;' : '' }} {{ ($needsPermSec && $isPS) || old('to_user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $needsPermSec && !$isPS ? '🔒 ' : '' }}{{ $u->name }} {{ $u->designation && $u->designation->name !== '—' ? ' (' . $u->designation->name . ')' : '' }} {{ $needsPermSec && !$isPS ? '(Locked: Needs PermSec Review)' : '' }}
                                </option>
                                @endforeach
                            </optgroup>
                            @endforeach
                        </select>

                        <div id="personSelectedBadge" class="mt-2" style="display:none;">
                            <span class="badge bg-success bg-opacity-10 text-success fw-600 px-3 py-2" style="font-size:.84rem;border-radius:8px;">
                                <i class="fa-solid fa-user-check me-1"></i>
                                Selected: <span id="personSelectedName"></span>
                            </span>
                        </div>

                        @error('to_user_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ── SECTION 2: Select Department Dropdown ───────────────── --}}
                    <div class="mb-3" id="deptSearchSection" style="display:none;">
                        <label for="departmentSelect" class="form-label fw-600">
                            Select Target Department <span class="text-danger">*</span>
                        </label>

                        <select id="departmentSelect"
                                class="form-select @error('department_id') is-invalid @enderror">
                            <option value="" disabled {{ old('department_id', $file->recommended_department_id) ? '' : 'selected' }}>— Choose target department —</option>

                            @foreach($departments as $dept)
                            @php
                                $isRecommended = ($file->recommended_department_id && (int)$file->recommended_department_id === (int)$dept->id);
                            @endphp
                            <option value="{{ $dept->id }}" {{ (old('department_id', $file->recommended_department_id) == $dept->id) ? 'selected' : '' }}>
                                {{ $isRecommended ? '⭐ Recommended: ' : '' }}{{ $dept->name }} {{ $dept->code ? ' (' . $dept->code . ')' : '' }}
                            </option>
                            @endforeach
                        </select>

                        <div id="deptSelectedBadge" class="mt-2" style="display:none;">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-600 px-3 py-2" style="font-size:.84rem;border-radius:8px;">
                                <i class="fa-solid fa-building-columns me-1"></i>
                                Target Department: <span id="deptSelectedName"></span>
                            </span>
                        </div>

                        @error('department_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    @endif {{-- end permsecLockedDept --}}

                    {{-- ── General Content Body / Message ──────────────────── --}}
                    <div class="mb-4">
                        <label for="remarksInput" class="form-label fw-600">
                            General Document Contents / Dispatch Message <span class="text-danger">*</span>
                        </label>
                        <textarea id="remarksInput"
                                  name="remarks"
                                  class="form-control @error('remarks') is-invalid @enderror"
                                  rows="4"
                                  placeholder="Write the general contents, instructions, or notes you want to send with this document…"
                                  required>{{ old('remarks') }}</textarea>
                        <div class="form-text text-muted">
                            <i class="fa-solid fa-circle-info me-1"></i>Write the general contents to be recorded in the file history.
                        </div>
                        @error('remarks')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    @php
                        $isPermSecUser  = ($authUser->designation?->name === 'Permanent Secretary' || $authUser->email === 'permsec@filetrack.local');
                        $canSetDeadline = $isPermSecUser || $isRecordsUser;
                    @endphp

                    @if($canSetDeadline)
                    {{-- ── Permanent Secretary & Records: Return Timeframe & Priority ── --}}
                    <div class="card mb-4 border-danger border-opacity-25 bg-danger bg-opacity-10" style="border-radius:10px;">
                        <div class="card-body p-3">
                            <h6 class="fw-700 text-danger mb-2">
                                <i class="fa-solid fa-clock-rotate-left me-1"></i>Return Timeframe &amp; Urgency (PermSec &amp; Records Only)
                            </h6>
                            <div class="row g-2 mb-1">
                                <div class="col-md-7">
                                    <label class="form-label fw-600 fs-xs mb-1">Return Time Limit</label>
                                    <select name="return_minutes" class="form-select form-select-sm">
                                        <option value="">— No Return Deadline —</option>
                                        <option value="30" {{ old('return_minutes') == '30' ? 'selected' : '' }}>30 Minutes</option>
                                        <option value="60" {{ old('return_minutes') == '60' ? 'selected' : '' }}>1 Hour</option>
                                        <option value="120" {{ old('return_minutes') == '120' ? 'selected' : '' }}>2 Hours</option>
                                        <option value="240" {{ old('return_minutes') == '240' ? 'selected' : '' }}>4 Hours</option>
                                        <option value="1440" {{ old('return_minutes') == '1440' ? 'selected' : '' }}>24 Hours (1 Day)</option>
                                    </select>
                                    <div class="form-text fs-xs text-muted">Required time frame for the document to return to Records.</div>
                                </div>
                                <div class="col-md-5 d-flex align-items-center pt-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_urgent" value="1" id="isUrgentSwitch" {{ old('is_urgent', $file->is_urgent) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-700 text-danger ms-1" for="isUrgentSwitch">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i>Mark Urgent
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn-portal-primary">
                            <i class="fa-solid fa-paper-plane me-1"></i> Send Document Now
                        </button>
                        <a href="{{ route('files.index') }}" class="btn-portal-outline">Cancel</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var btnModePerson = document.getElementById('btnModePerson');
    var btnModeDept   = document.getElementById('btnModeDept');
    var personSection = document.getElementById('personSection');
    var deptSearchSection = document.getElementById('deptSearchSection');

    var inpDestType  = document.getElementById('destination_type');
    var inpToUser    = document.getElementById('to_user_id');
    var inpDeptId    = document.getElementById('department_id');

    var personSelect = document.getElementById('personSelect');
    var userSearchInput = document.getElementById('userSearchInput');
    var userSearchResults = document.getElementById('userSearchResults');
    var personSelectedBadge = document.getElementById('personSelectedBadge');
    var personSelectedName = document.getElementById('personSelectedName');

    var departmentSelect  = document.getElementById('departmentSelect');
    var deptSelectedBadge = document.getElementById('deptSelectedBadge');
    var deptSelectedName  = document.getElementById('deptSelectedName');

    var searchTimer = null;

    btnModePerson.addEventListener('click', function() {
        setMode('user');
    });

    btnModeDept.addEventListener('click', function() {
        setMode('department');
    });

    function setMode(mode) {
        if (mode === 'user') {
            btnModePerson.classList.add('active');
            btnModeDept.classList.remove('active');
            personSection.style.display = '';
            deptSearchSection.style.display = 'none';
            inpDestType.value = 'user';
            inpDeptId.value = '';
        } else {
            btnModeDept.classList.add('active');
            btnModePerson.classList.remove('active');
            deptSearchSection.style.display = '';
            personSection.style.display = 'none';
            inpDestType.value = 'department';
            inpToUser.value = '';
            if (departmentSelect && departmentSelect.value) {
                inpDeptId.value = departmentSelect.value;
            }
        }
    }

    if (departmentSelect && departmentSelect.value) {
        inpDeptId.value = departmentSelect.value;
        var selectedDeptOpt = departmentSelect.options[departmentSelect.selectedIndex];
        if (selectedDeptOpt) {
            deptSelectedName.textContent = selectedDeptOpt.text;
            deptSelectedBadge.style.display = '';
        }
    }

    if (departmentSelect) {
        departmentSelect.addEventListener('change', function() {
            var dId = departmentSelect.value;
            if (dId) {
                inpDeptId.value = dId;
                var optText = departmentSelect.options[departmentSelect.selectedIndex].text;
                deptSelectedName.textContent = optText;
                deptSelectedBadge.style.display = '';
            }
        });
    }

    if (personSelect.value) {
        inpToUser.value = personSelect.value;
        var selectedOpt = personSelect.options[personSelect.selectedIndex];
        if (selectedOpt) {
            personSelectedName.textContent = selectedOpt.text;
            personSelectedBadge.style.display = '';
        }
    }

    personSelect.addEventListener('change', function() {
        var uId = personSelect.value;
        if (uId) {
            inpToUser.value = uId;
            var optText = personSelect.options[personSelect.selectedIndex].text;
            personSelectedName.textContent = optText;
            personSelectedBadge.style.display = '';
        }
    });

    userSearchInput.addEventListener('input', function() {
        var q = userSearchInput.value.trim();
        clearTimeout(searchTimer);

        if (q.length < 2) {
            userSearchResults.style.display = 'none';
            return;
        }

        searchTimer = setTimeout(function() {
            fetch('{{ route("ajax.users.search") }}?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                userSearchResults.innerHTML = '';
                if (!data || !data.length) {
                    userSearchResults.style.display = 'none';
                    return;
                }
                data.forEach(function(u) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action d-flex align-items-center gap-2 py-2 px-3';
                    btn.innerHTML = '<i class="fa-solid fa-user text-success"></i><div><strong>' + esc(u.name) + '</strong> <span class="text-muted ms-1">(' + esc(u.dept_name) + ' — ' + esc(u.designation_name) + ')</span></div>';
                    btn.addEventListener('click', function() {
                        inpToUser.value = u.id;
                        personSelect.value = u.id;
                        personSelectedName.textContent = u.name + ' (' + u.dept_name + ')';
                        personSelectedBadge.style.display = '';
                        userSearchResults.style.display = 'none';
                    });
                    userSearchResults.appendChild(btn);
                });
                userSearchResults.style.display = '';
            });
        }, 250);
    });

    deptSearchInput.addEventListener('input', function() {
        var q = deptSearchInput.value.trim();
        clearTimeout(searchTimer);

        if (q.length < 2) {
            deptResultsList.style.display = 'none';
            return;
        }

        searchTimer = setTimeout(function() {
            fetch('{{ route("ajax.departments.search") }}?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                deptResultsList.innerHTML = '';
                if (!data || !data.length) {
                    deptResultsList.style.display = 'none';
                    return;
                }
                data.forEach(function(d) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action d-flex align-items-center gap-2 py-2 px-3';
                    btn.innerHTML = '<i class="fa-solid fa-building-columns text-primary"></i><span>' + esc(d.name) + '</span>';
                    btn.addEventListener('click', function() {
                        inpDeptId.value = d.id;
                        deptSelectedName.textContent = d.name;
                        deptSelectedBadge.style.display = '';
                        deptResultsList.style.display = 'none';
                    });
                    deptResultsList.appendChild(btn);
                });
                deptResultsList.style.display = '';
            });
        }, 250);
    });

    deptClearBtn.addEventListener('click', function() {
        inpDeptId.value = '';
        deptSelectedBadge.style.display = 'none';
    });

    document.addEventListener('click', function(e) {
        if (!userSearchInput.contains(e.target) && !userSearchResults.contains(e.target)) {
            userSearchResults.style.display = 'none';
        }
        if (!deptSearchInput.contains(e.target) && !deptResultsList.contains(e.target)) {
            deptResultsList.style.display = 'none';
        }
    });

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s || '';
        return d.innerHTML;
    }
})();
</script>
@endpush
