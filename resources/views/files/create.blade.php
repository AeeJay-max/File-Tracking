@extends('layouts.app')
@section('title', 'Create File')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('files.index') }}">Files</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Create New File</h1>
        <div class="page-subtitle">Register a new official document in the system</div>
    </div>
    <a href="{{ route('files.index') }}" class="btn-portal-outline">
        <i class="fa-solid fa-arrow-left"></i> Back
    </a>
</div>

<div class="portal-form-card">
    <form action="{{ route('files.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="portal-form"
          id="createFileForm"
          novalidate>
        @csrf

        {{-- Government File Number --}}
        <div class="mb-3">
            <label class="form-label">
                Government File Number <span class="required-star">*</span>
            </label>
            <input type="text"
                   name="file_number"
                   class="form-control @error('file_number') is-invalid @enderror"
                   value="{{ old('file_number') }}"
                   placeholder="e.g. HR/FIN/2026/234  or  FIN-12/456"
                   required
                   autocomplete="off">
            <div class="form-text text-muted">
                <i class="fa-solid fa-circle-info me-1"></i>
                Must be unique. Allowed: letters, numbers, hyphens, slashes, dots, spaces.
            </div>
            @error('file_number')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- File Name --}}
        <div class="mb-3">
            <label class="form-label">
                File Name / Subject <span class="required-star">*</span>
            </label>
            <input type="text"
                   name="file_name"
                   class="form-control @error('file_name') is-invalid @enderror"
                   value="{{ old('file_name') }}"
                   placeholder="Enter file name or subject"
                   required>
            @error('file_name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Department — searchable input with inline creation --}}
        <div class="mb-3">
            <label for="deptSearchField" class="form-label">
                Department <span class="required-star">*</span>
            </label>

            {{-- Hidden field submitted to the controller --}}
            <input type="hidden"
                   name="department_id"
                   id="deptIdHidden"
                   value="{{ old('department_id', auth()->user()->department_id) }}">

            <div class="position-relative" id="deptSearchWrap">

                <input type="text"
                       id="deptSearchField"
                       class="form-control @error('department_id') is-invalid @enderror"
                       placeholder="Type to search department…"
                       autocomplete="off"
                       value="{{ old('_dept_label',
                           $departments->firstWhere('id', old('department_id', auth()->user()->department_id))?->name ?? ''
                       ) }}"
                       aria-autocomplete="list"
                       aria-controls="deptResultsList"
                       aria-expanded="false">

                {{-- Results dropdown --}}
                <div id="deptResultsList"
                     class="list-group shadow"
                     role="listbox"
                     style="display:none;
                            position:absolute;
                            z-index:1055;
                            width:100%;
                            top:calc(100% + 3px);
                            max-height:240px;
                            overflow-y:auto;
                            border-radius:8px;">
                </div>
            </div>

            {{-- Selected dept badge --}}
            <div id="deptSelectedBadge"
                 class="mt-2"
                 style="display:{{ old('department_id', auth()->user()->department_id) ? '' : 'none' }};">
                <span class="badge bg-primary bg-opacity-10 text-primary fw-600 py-2 px-3"
                      style="font-size:.8rem;border-radius:8px;">
                    <i class="fa-solid fa-building-columns me-1"></i>
                    <span id="deptSelectedName">
                        {{ $departments->firstWhere('id', old('department_id', auth()->user()->department_id))?->name ?? '' }}
                    </span>
                    <button type="button"
                            id="deptClearBtn"
                            class="btn-close btn-close-sm ms-2"
                            style="font-size:.55rem;"
                            aria-label="Clear department selection"></button>
                </span>
            </div>

            {{-- "No department found" + Create button --}}
            <div id="deptNoResult" class="mt-2" style="display:none;">
                <span class="text-danger" style="font-size:.85rem;">
                    <i class="fa-solid fa-circle-xmark me-1"></i>No department found.
                </span>
                <button type="button"
                        id="openCreateDeptBtn"
                        class="btn btn-sm btn-outline-primary ms-2"
                        data-bs-toggle="modal"
                        data-bs-target="#createDeptModal"
                        style="font-size:.8rem;">
                    <i class="fa-solid fa-plus me-1"></i>Create New Department
                </button>
            </div>

            <div class="form-text text-muted mt-1">
                <i class="fa-solid fa-circle-info me-1"></i>
                Select the department this file belongs to.
            </div>

            @error('department_id')
            <div class="text-danger" style="font-size:.875rem;margin-top:.25rem;">{{ $message }}</div>
            @enderror
        </div>

        {{-- Remarks --}}
        <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea name="remarks"
                      class="form-control @error('remarks') is-invalid @enderror"
                      rows="3"
                      placeholder="Optional remarks or notes">{{ old('remarks') }}</textarea>
            @error('remarks')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Attachment --}}
        <div class="mb-4">
            <label class="form-label">Upload Document</label>
            <input type="file"
                   name="attachment"
                   class="form-control @error('attachment') is-invalid @enderror"
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png">
            <div class="form-text text-muted">Max 10 MB. Allowed: PDF, Word, Excel, PowerPoint, Images.</div>
            @error('attachment')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn-portal-primary">
                <i class="fa-solid fa-floppy-disk me-1"></i> Save File
            </button>
            <a href="{{ route('files.index') }}" class="btn-portal-outline">Cancel</a>
        </div>

    </form>
</div>

{{-- ═══════════════════════════════════════════════════════════
     CREATE DEPARTMENT MODAL  (all authenticated users)
═══════════════════════════════════════════════════════════ --}}
<div class="modal fade"
     id="createDeptModal"
     tabindex="-1"
     aria-labelledby="createDeptModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 12px 40px rgba(15,23,42,.18);">

            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800" id="createDeptModalLabel">
                    <i class="fa-solid fa-building-columns me-2 text-primary"></i>Create New Department
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-3">

                {{-- Modal-level alert (success / error) --}}
                <div id="deptModalAlert" class="alert d-none mb-3" role="alert"></div>

                <form id="createDeptForm" novalidate>

                    <div class="mb-3">
                        <label for="newDeptName" class="form-label fw-600">
                            Department Name <span class="required-star">*</span>
                        </label>
                        <input type="text"
                               id="newDeptName"
                               class="form-control"
                               placeholder="e.g. Human Resources"
                               maxlength="255"
                               autocomplete="off">
                        <div id="newDeptNameError" class="invalid-feedback"></div>
                    </div>

                    <div class="form-text text-muted mb-3" style="font-size:.8rem;">
                        <i class="fa-solid fa-info-circle me-1"></i>
                        A unique department code will be generated automatically.
                    </div>

                </form>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button"
                        id="saveDeptBtn"
                        class="btn btn-primary px-4 fw-600">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Save Department
                </button>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    /*
     * Departments are embedded from the server on initial load.
     * After inline creation, we push the new dept into this array.
     */
    var departments = @json($departments->map(fn($d) => ['id' => $d->id, 'name' => $d->name])->values());

    var searchField   = document.getElementById('deptSearchField');
    var hiddenInput   = document.getElementById('deptIdHidden');
    var resultsList   = document.getElementById('deptResultsList');
    var selectedBadge = document.getElementById('deptSelectedBadge');
    var selectedName  = document.getElementById('deptSelectedName');
    var clearBtn      = document.getElementById('deptClearBtn');
    var noResult      = document.getElementById('deptNoResult');
    var csrf          = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    /* ── Render results ────────────────────────────────────────── */
    function renderResults(items) {
        resultsList.innerHTML = '';

        if (!items.length) {
            noResult.style.display    = '';
            resultsList.style.display = 'none';
            return;
        }

        noResult.style.display = 'none';

        items.forEach(function (dept) {
            var btn = document.createElement('button');
            btn.type      = 'button';
            btn.className = 'list-group-item list-group-item-action d-flex align-items-center gap-2 py-2 px-3';
            btn.style.fontSize = '.88rem';
            btn.innerHTML =
                '<i class="fa-solid fa-building-columns text-primary fa-sm"></i>' +
                '<span>' + esc(dept.name) + '</span>';
            btn.addEventListener('mousedown', function (e) {
                e.preventDefault(); // prevent blur closing list first
                selectDept(dept.id, dept.name);
            });
            resultsList.appendChild(btn);
        });

        resultsList.style.display = '';
        searchField.setAttribute('aria-expanded', 'true');
    }

    /* ── Filter ────────────────────────────────────────────────── */
    function filterDepts(q) {
        if (!q.trim()) return departments;
        var lower = q.toLowerCase();
        return departments.filter(function (d) {
            return d.name.toLowerCase().indexOf(lower) !== -1;
        });
    }

    /* ── Select a dept ─────────────────────────────────────────── */
    function selectDept(id, name) {
        hiddenInput.value        = id;
        searchField.value        = name;
        selectedName.textContent = name;
        selectedBadge.style.display = '';
        resultsList.style.display   = 'none';
        resultsList.innerHTML       = '';
        noResult.style.display      = 'none';
        searchField.setAttribute('aria-expanded', 'false');
        searchField.classList.remove('is-invalid');
    }

    /* ── Clear ─────────────────────────────────────────────────── */
    function clearSelection() {
        hiddenInput.value        = '';
        searchField.value        = '';
        selectedBadge.style.display = 'none';
        selectedName.textContent    = '';
        resultsList.style.display   = 'none';
        resultsList.innerHTML       = '';
        noResult.style.display      = 'none';
        searchField.focus();
    }

    clearBtn.addEventListener('click', clearSelection);

    /* ── Typing ─────────────────────────────────────────────────── */
    searchField.addEventListener('input', function () {
        hiddenInput.value           = '';
        selectedBadge.style.display = 'none';
        noResult.style.display      = 'none';

        var q = searchField.value;
        if (!q.trim()) {
            resultsList.style.display = 'none';
            resultsList.innerHTML     = '';
            return;
        }
        renderResults(filterDepts(q));
    });

    /* ── Focus ─────────────────────────────────────────────────── */
    searchField.addEventListener('focus', function () {
        if (!searchField.value.trim() && !hiddenInput.value) {
            renderResults(departments);
        } else if (searchField.value.trim() && !hiddenInput.value) {
            renderResults(filterDepts(searchField.value));
        }
    });

    /* ── Click outside ─────────────────────────────────────────── */
    document.addEventListener('click', function (e) {
        var wrap = document.getElementById('deptSearchWrap');
        if (wrap && !wrap.contains(e.target)) {
            resultsList.style.display = 'none';
            searchField.setAttribute('aria-expanded', 'false');
            if (searchField.value.trim() && !hiddenInput.value) {
                noResult.style.display = '';
                searchField.classList.add('is-invalid');
            }
        }
    });

    /* ── Keyboard navigation ────────────────────────────────────── */
    searchField.addEventListener('keydown', function (e) {
        var items = resultsList.querySelectorAll('.list-group-item');
        if (!items.length) return;

        var focused = resultsList.querySelector('.list-group-item:focus');
        var idx     = Array.from(items).indexOf(focused);

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            (items[idx + 1] || items[0]).focus();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            (items[idx - 1] || items[items.length - 1]).focus();
        } else if (e.key === 'Escape') {
            resultsList.style.display = 'none';
            searchField.focus();
        }
    });

    /* ── Form submit guard ─────────────────────────────────────── */
    document.getElementById('createFileForm').addEventListener('submit', function (e) {
        if (!hiddenInput.value) {
            e.preventDefault();
            searchField.classList.add('is-invalid');
            noResult.style.display = '';
            var span = noResult.querySelector('span');
            if (span) span.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i>Please select a department from the list.';
            searchField.focus();
        }
    });

    /* ═══════════════════════════════════════════════════════════
       INLINE DEPARTMENT CREATION  (modal — all authenticated users)
    ═══════════════════════════════════════════════════════════ */
    var saveDeptBtn    = document.getElementById('saveDeptBtn');
    var newDeptName    = document.getElementById('newDeptName');
    var newDeptNameErr = document.getElementById('newDeptNameError');
    var deptModalAlert = document.getElementById('deptModalAlert');

    if (saveDeptBtn && newDeptName) {

        /* Pre-fill the modal name field with what the user typed in search */
        var createDeptModal = document.getElementById('createDeptModal');
        if (createDeptModal) {
            createDeptModal.addEventListener('show.bs.modal', function () {
                newDeptName.value = searchField.value.trim();
                newDeptName.classList.remove('is-invalid');
                if (newDeptNameErr) newDeptNameErr.textContent = '';
                if (deptModalAlert) deptModalAlert.className = 'alert d-none mb-3';
            });
            createDeptModal.addEventListener('shown.bs.modal', function () {
                newDeptName.focus();
                newDeptName.select();
            });
        }

        /* ── Submit via AJAX ─────────────────────────────────── */
        saveDeptBtn.addEventListener('click', function () {
            var name = newDeptName.value.trim();

            // Client-side required check
            if (!name) {
                newDeptName.classList.add('is-invalid');
                if (newDeptNameErr) newDeptNameErr.textContent = 'Department name is required.';
                newDeptName.focus();
                return;
            }

            // Reset previous errors
            newDeptName.classList.remove('is-invalid');
            if (newDeptNameErr) newDeptNameErr.textContent = '';
            if (deptModalAlert) deptModalAlert.className = 'alert d-none mb-3';

            // Disable button to prevent double-submit
            saveDeptBtn.disabled = true;
            saveDeptBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

            fetch('{{ route("ajax.departments.create") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ name: name }),
            })
            .then(function (r) { return r.json().then(function (d) { return { status: r.status, data: d }; }); })
            .then(function (res) {
                saveDeptBtn.disabled = false;
                saveDeptBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i>Save Department';

                if (res.data.success) {
                    var dept = res.data.department;

                    // Add to local list so it shows in search immediately
                    departments.push({ id: dept.id, name: dept.name });

                    // Auto-select the new department
                    selectDept(dept.id, dept.name);

                    // Close the modal
                    var modal = bootstrap.Modal.getInstance(document.getElementById('createDeptModal'));
                    if (modal) modal.hide();

                    // Brief success flash near the field (no page reload)
                    var flash = document.createElement('div');
                    flash.className = 'alert alert-success py-2 px-3 mt-2';
                    flash.style.fontSize = '.85rem';
                    flash.style.borderRadius = '8px';
                    flash.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i>Department "' + esc(dept.name) + '" created and selected.';
                    var deptWrap = document.getElementById('deptSearchWrap');
                    deptWrap.parentNode.insertBefore(flash, deptWrap.nextSibling);
                    setTimeout(function () { flash.remove(); }, 4000);

                } else if (res.status === 422 && res.data.errors && res.data.errors.name) {
                    // Laravel validation error
                    newDeptName.classList.add('is-invalid');
                    if (newDeptNameErr) newDeptNameErr.textContent = res.data.errors.name[0];
                } else {
                    // Other error (403, 500, etc.)
                    var msg = res.data.message || 'An error occurred. Please try again.';
                    if (deptModalAlert) {
                        deptModalAlert.className = 'alert alert-danger mb-3';
                        deptModalAlert.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i>' + esc(msg);
                    }
                }
            })
            .catch(function () {
                saveDeptBtn.disabled = false;
                saveDeptBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i>Save Department';
                if (deptModalAlert) {
                    deptModalAlert.className = 'alert alert-danger mb-3';
                    deptModalAlert.textContent = 'Network error. Please check your connection and try again.';
                }
            });
        });

        /* Allow Enter key inside the modal input to trigger save */
        newDeptName.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveDeptBtn.click();
            }
        });
    }

    /* ── Utility ─────────────────────────────────────────────── */
    function esc(str) {
        var d = document.createElement('div');
        d.textContent = str || '';
        return d.innerHTML;
    }

})();
</script>
@endpush
