@extends('layouts.app')
@section('title', 'Transfer File')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('files.index') }}">Files</a></li>
<li class="breadcrumb-item active">Transfer</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Transfer File</h1>
        <div class="page-subtitle">Route this file to another user or department</div>
    </div>
    <a href="{{ route('files.index') }}" class="btn-portal-outline">
        <i class="fa-solid fa-arrow-left"></i> Back
    </a>
</div>

<div class="row g-3 justify-content-center">

    {{-- File Summary --}}
    <div class="col-md-4 col-lg-3">
        <div class="portal-card">
            <div class="card-header">
                <i class="fa-solid fa-file-lines me-2 text-primary"></i>File Details
            </div>
            <div class="card-body">
                <dl class="mb-0" style="display:grid;grid-template-columns:auto 1fr;gap:6px 12px;font-size:.85rem;">
                    <dt class="text-muted fw-600">File No.</dt>
                    <dd class="fw-700 text-portal-primary mb-0">{{ $file->file_number }}</dd>

                    <dt class="text-muted fw-600">Name</dt>
                    <dd class="fw-600 mb-0">{{ $file->file_name }}</dd>

                    <dt class="text-muted fw-600">Department</dt>
                    <dd class="mb-0">{{ $file->department->name ?? 'N/A' }}</dd>

                    <dt class="text-muted fw-600">Holder</dt>
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
                <i class="fa-solid fa-right-left me-2 text-primary"></i>Transfer Details
            </div>
            <div class="card-body">

                <form action="{{ route('files.transfer.store') }}"
                      method="POST"
                      id="transferForm"
                      novalidate>
                    @csrf

                    {{-- Hidden fields sent to the controller (unchanged) --}}
                    <input type="hidden" name="file_record_uuid" value="{{ $file->uuid }}">
                    <input type="hidden" name="destination_type" id="destination_type" value="{{ old('destination_type') }}">
                    <input type="hidden" name="to_user_id"       id="to_user_id"       value="{{ old('to_user_id') }}">
                    <input type="hidden" name="department_id"    id="department_id"    value="{{ old('department_id') }}">

                    {{-- ── Transfer To (select) ──────────────────── --}}
                    <div class="mb-3">
                        <label for="recipientSelect" class="form-label fw-600">
                            Transfer To <span class="text-danger">*</span>
                        </label>

                        <select id="recipientSelect"
                                class="form-select @error('destination_type') is-invalid @enderror @error('to_user_id') is-invalid @enderror"
                                required>
                            <option value="" disabled {{ old('to_user_id') || old('destination_type') === 'other' ? '' : 'selected' }}>
                                — Select recipient —
                            </option>

                            {{-- Same-dept users --}}
                            @forelse($sameDeptUsers as $u)
                            <option value="user:{{ $u->id }}"
                                {{ old('to_user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}{{ $u->designation && $u->designation->name !== '—' ? ' — ' . $u->designation->name : '' }}
                            </option>
                            @empty
                            {{-- no users — still show Other Dept --}}
                            @endforelse

                            {{-- Separator + Other Department --}}
                            <option value="other" {{ old('destination_type') === 'other' ? 'selected' : '' }}>
                                ── Other Department ──
                            </option>
                        </select>

                        @error('destination_type')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('to_user_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ── Department Search (disabled by default) ── --}}
                    <div class="mb-3" id="deptSearchSection">
                        <label for="deptSearchInput" class="form-label fw-600 text-muted" id="deptSearchLabel">
                            <i class="fa-solid fa-building-columns me-1"></i>Department Search
                        </label>

                        <div class="position-relative">
                            <input type="text"
                                   id="deptSearchInput"
                                   class="form-control @error('department_id') is-invalid @enderror"
                                   placeholder="Type to search department…"
                                   autocomplete="off"
                                   disabled
                                   value="{{ old('_dept_display', '') }}">

                            {{-- AJAX results list --}}
                            <div id="deptResultsList"
                                 class="list-group shadow-sm"
                                 style="display:none;position:absolute;z-index:1055;width:100%;top:calc(100% + 2px);border-radius:8px;overflow:hidden;">
                            </div>
                        </div>

                        <div id="deptSelectedBadge"
                             class="mt-2"
                             style="display:{{ old('department_id') ? '' : 'none' }};">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-600 px-3 py-2"
                                  style="font-size:.8rem;border-radius:8px;">
                                <i class="fa-solid fa-check me-1"></i>
                                <span id="deptSelectedName">{{ old('_dept_display', '') }}</span>
                                <button type="button"
                                        id="deptClearBtn"
                                        class="btn-close btn-close-sm ms-2"
                                        style="font-size:.6rem;"
                                        aria-label="Clear department"></button>
                            </span>
                        </div>

                        <div id="deptNoResult" class="form-text text-danger mt-1" style="display:none;">
                            <i class="fa-solid fa-circle-xmark me-1"></i>No department found. Please select from the list.
                        </div>

                        @error('department_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ── Remarks ────────────────────────────────── --}}
                    <div class="mb-4">
                        <label for="remarksInput" class="form-label fw-600">Remarks</label>
                        <textarea id="remarksInput"
                                  name="remarks"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Optional notes or instructions for the recipient…">{{ old('remarks') }}</textarea>
                        <div class="form-text text-muted">
                            Remarks are saved to the file journey timeline.
                        </div>
                    </div>

                    {{-- ── Submit ──────────────────────────────────── --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn-portal-primary" id="submitBtn">
                            <i class="fa-solid fa-paper-plane me-1"></i>Transfer File
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
    var recipientSelect  = document.getElementById('recipientSelect');
    var deptSearchInput  = document.getElementById('deptSearchInput');
    var deptResultsList  = document.getElementById('deptResultsList');
    var deptSelectedBadge= document.getElementById('deptSelectedBadge');
    var deptSelectedName = document.getElementById('deptSelectedName');
    var deptClearBtn     = document.getElementById('deptClearBtn');
    var deptNoResult     = document.getElementById('deptNoResult');
    var deptSearchLabel  = document.getElementById('deptSearchLabel');

    var inpDestType  = document.getElementById('destination_type');
    var inpToUser    = document.getElementById('to_user_id');
    var inpDeptId    = document.getElementById('department_id');

    var searchTimer  = null;
    var csrf         = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    /* ── Initialise from old() on validation failure ──────── */
    (function init() {
        var destType = inpDestType.value;
        if (destType === 'other') {
            enableDeptSearch();
        }
    })();

    /* ── Handle recipient selection ───────────────────────── */
    recipientSelect.addEventListener('change', function () {
        var val = recipientSelect.value;

        if (!val) return;

        if (val === 'other') {
            // Other Department selected
            inpDestType.value = 'other';
            inpToUser.value   = '';
            enableDeptSearch();
        } else if (val.startsWith('user:')) {
            // A same-dept user selected
            inpDestType.value = 'same';
            inpToUser.value   = val.replace('user:', '');
            inpDeptId.value   = '';
            disableDeptSearch();
        }
    });

    /* ── Enable dept search field ─────────────────────────── */
    function enableDeptSearch() {
        deptSearchInput.disabled = false;
        deptSearchInput.classList.remove('bg-light');
        deptSearchLabel.classList.remove('text-muted');
        deptSearchLabel.classList.add('text-dark');
        deptSearchInput.focus();
    }

    /* ── Disable dept search field and clear values ────────── */
    function disableDeptSearch() {
        deptSearchInput.disabled = true;
        deptSearchInput.value    = '';
        deptSearchInput.classList.add('bg-light');
        deptSearchLabel.classList.add('text-muted');
        deptSearchLabel.classList.remove('text-dark');
        inpDeptId.value = '';
        deptSelectedBadge.style.display = 'none';
        deptSelectedName.textContent    = '';
        deptResultsList.style.display   = 'none';
        deptResultsList.innerHTML       = '';
        deptNoResult.style.display      = 'none';
    }

    /* ── AJAX dept search while typing ──────────────────────── */
    deptSearchInput.addEventListener('input', function () {
        var q = deptSearchInput.value.trim();

        // Clear selected dept when user starts retyping
        inpDeptId.value = '';
        deptSelectedBadge.style.display = 'none';
        deptNoResult.style.display      = 'none';

        clearTimeout(searchTimer);

        if (q.length < 2) {
            deptResultsList.style.display = 'none';
            deptResultsList.innerHTML     = '';
            return;
        }

        searchTimer = setTimeout(function () { fetchDepts(q); }, 260);
    });

    function fetchDepts(q) {
        fetch('{{ route("ajax.departments.search") }}?q=' + encodeURIComponent(q), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf
            },
            credentials: 'same-origin'
        })
        .then(function (r) { return r.ok ? r.json() : []; })
        .then(renderResults)
        .catch(function () {
            deptResultsList.style.display = 'none';
        });
    }

    function renderResults(data) {
        deptResultsList.innerHTML = '';

        if (!data || !data.length) {
            deptNoResult.style.display    = '';
            deptResultsList.style.display = 'none';
            return;
        }

        deptNoResult.style.display = 'none';

        data.forEach(function (dept) {
            var btn = document.createElement('button');
            btn.type      = 'button';
            btn.className = 'list-group-item list-group-item-action d-flex align-items-center gap-2 py-2 px-3';
            btn.style.fontSize = '.88rem';
            btn.innerHTML =
                '<i class="fa-solid fa-building-columns text-primary fa-sm"></i>' +
                '<span>' + escHtml(dept.name) + '</span>';
            btn.addEventListener('click', function () { selectDept(dept.id, dept.name); });
            deptResultsList.appendChild(btn);
        });

        deptResultsList.style.display = '';
    }

    /* ── Confirm dept selection ──────────────────────────────── */
    function selectDept(id, name) {
        inpDeptId.value             = id;
        deptSearchInput.value       = name;
        deptSelectedName.textContent= name;
        deptSelectedBadge.style.display = '';
        deptResultsList.style.display   = 'none';
        deptResultsList.innerHTML       = '';
        deptNoResult.style.display      = 'none';
    }

    /* ── Clear dept selection ────────────────────────────────── */
    deptClearBtn.addEventListener('click', function () {
        inpDeptId.value             = '';
        deptSearchInput.value       = '';
        deptSelectedBadge.style.display = 'none';
        deptSelectedName.textContent    = '';
        deptResultsList.style.display   = 'none';
        deptResultsList.innerHTML       = '';
        deptSearchInput.focus();
    });

    /* ── Hide results when clicking outside ─────────────────── */
    document.addEventListener('click', function (e) {
        if (!deptSearchInput.contains(e.target) && !deptResultsList.contains(e.target)) {
            deptResultsList.style.display = 'none';
        }
    });

    /* ── Form submit validation ──────────────────────────────── */
    document.getElementById('transferForm').addEventListener('submit', function (e) {
        var dest = inpDestType.value;

        if (!dest) {
            e.preventDefault();
            recipientSelect.classList.add('is-invalid');
            recipientSelect.focus();
            return;
        }

        if (dest === 'same' && !inpToUser.value) {
            e.preventDefault();
            recipientSelect.classList.add('is-invalid');
            recipientSelect.focus();
            return;
        }

        if (dest === 'other' && !inpDeptId.value) {
            e.preventDefault();
            deptNoResult.style.display  = '';
            deptNoResult.textContent    = 'Please select a department from the search results.';
            deptSearchInput.classList.add('is-invalid');
            deptSearchInput.focus();
            return;
        }

        recipientSelect.classList.remove('is-invalid');
        deptSearchInput.classList.remove('is-invalid');
    });

    /* ── Utility: safe HTML escape ───────────────────────────── */
    function escHtml(str) {
        var d = document.createElement('div');
        d.textContent = str || '';
        return d.innerHTML;
    }

})();
</script>
@endpush
