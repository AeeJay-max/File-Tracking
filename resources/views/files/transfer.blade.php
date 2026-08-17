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

                    {{-- ── Mode Switcher ────────────────────────────────────── --}}
                    <div class="mb-3">
                        <label class="form-label fw-600">Send Target <span class="text-danger">*</span></label>
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-primary active fw-600" id="btnModePerson">
                                <i class="fa-solid fa-user me-1"></i> Send Directly to Person
                            </button>
                            <button type="button" class="btn btn-outline-primary fw-600" id="btnModeDept">
                                <i class="fa-solid fa-building-columns me-1"></i> Send to Department
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
                                   placeholder="Search person by name, department, or designation…"
                                   autocomplete="off">

                            {{-- AJAX Person Search Results --}}
                            <div id="userSearchResults"
                                 class="list-group shadow"
                                 style="display:none;position:absolute;z-index:1055;width:100%;top:calc(100% + 2px);border-radius:8px;overflow:hidden;">
                            </div>
                        </div>

                        <select id="personSelect"
                                class="form-select @error('to_user_id') is-invalid @enderror">
                            <option value="" disabled selected>— Choose recipient person —</option>

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
                                <option value="{{ $u->id }}" {{ old('to_user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} {{ $u->designation && $u->designation->name !== '—' ? ' (' . $u->designation->name . ')' : '' }}
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

                    {{-- ── SECTION 2: Department Search ────────────────────── --}}
                    <div class="mb-3" id="deptSearchSection" style="display:none;">
                        <label for="deptSearchInput" class="form-label fw-600">
                            Select Department <span class="text-danger">*</span>
                        </label>

                        <div class="position-relative">
                            <input type="text"
                                   id="deptSearchInput"
                                   class="form-control @error('department_id') is-invalid @enderror"
                                   placeholder="Type to search department…"
                                   autocomplete="off">

                            <div id="deptResultsList"
                                 class="list-group shadow"
                                 style="display:none;position:absolute;z-index:1055;width:100%;top:calc(100% + 2px);border-radius:8px;overflow:hidden;">
                            </div>
                        </div>

                        <div id="deptSelectedBadge" class="mt-2" style="display:{{ old('department_id') ? '' : 'none' }};">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-600 px-3 py-2" style="font-size:.8rem;border-radius:8px;">
                                <i class="fa-solid fa-building-columns me-1"></i>
                                <span id="deptSelectedName">{{ old('_dept_display', '') }}</span>
                                <button type="button" id="deptClearBtn" class="btn-close btn-close-sm ms-2" style="font-size:.6rem;"></button>
                            </span>
                        </div>

                        @error('department_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

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

    var deptSearchInput = document.getElementById('deptSearchInput');
    var deptResultsList = document.getElementById('deptResultsList');
    var deptSelectedBadge = document.getElementById('deptSelectedBadge');
    var deptSelectedName = document.getElementById('deptSelectedName');
    var deptClearBtn = document.getElementById('deptClearBtn');

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
