{{-- ═══════════════════════════════════════════════════════════
     GLOBAL CREATE FILE OVERLAY MODAL
═══════════════════════════════════════════════════════════ --}}
<div class="modal fade"
     id="createFileModal"
     tabindex="-1"
     aria-labelledby="createFileModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 12px 40px rgba(15,23,42,.18);">

            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800" id="createFileModalLabel">
                    <i class="fa-solid fa-file-circle-plus me-2 text-primary"></i>Register New File
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-3">
                <form action="{{ route('files.store') }}"
                      method="POST"
                      enctype="multipart/form-data"
                      id="modalCreateFileForm"
                      novalidate>
                    @csrf

                    {{-- Folio Number --}}
                    <div class="mb-3">
                        <label class="form-label fw-600">
                            Folio Number <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="file_number"
                               class="form-control"
                               placeholder="e.g. HR/FIN/2026/234  or  FIN-12/456"
                               required
                               autocomplete="off">
                        <div class="form-text text-muted fs-xs">
                            <i class="fa-solid fa-circle-info me-1"></i>Must be unique. Allowed: letters, numbers, hyphens, slashes, dots, spaces.
                        </div>
                    </div>

                    {{-- Folder Selection (Folder Number & Auto-Filled Folder Name) --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">
                                Folder Number <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <select name="folder_number"
                                        id="overlayFolderNumberSelect"
                                        class="form-select"
                                        required>
                                    <option value="" disabled selected>— Select Folder Number —</option>
                                    @foreach($globalFolders ?? [] as $f)
                                    <option value="{{ $f->folder_number }}" data-name="{{ $f->folder_name }}">
                                        {{ $f->folder_number }} — {{ $f->folder_name }}
                                    </option>
                                    @endforeach
                                </select>
                                @php
                                $u = auth()->user();
                                $isRecordsAdmin = $u && $u->role === 'admin' && $u->department && (strtoupper((string)$u->department->code) === 'REC' || \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower((string)$u->department->name), 'record'));
                                @endphp
                                @if($isRecordsAdmin)
                                <button type="button"
                                        class="btn btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#createFolderModal"
                                        title="Add New Folder">
                                    <i class="fa-solid fa-folder-plus me-1"></i>New
                                </button>
                                @endif
                            </div>
                            <div class="form-text text-muted fs-xs">
                                <i class="fa-solid fa-circle-info me-1"></i>Select folder number or create a new folder.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">
                                Folder Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="folder_name"
                                   id="overlayFolderNameInput"
                                   class="form-control"
                                   placeholder="Folder name auto-fills when number is selected"
                                   required>
                            <div class="form-text text-muted fs-xs">
                                <i class="fa-solid fa-magic me-1"></i>Auto-filled upon selecting Folder Number.
                            </div>
                        </div>
                    </div>

                    {{-- File Name--}}
                    <div class="mb-3">
                        <label class="form-label fw-600">
                            File Name<span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="file_name"
                               class="form-control"
                               placeholder="Enter file name or subject"
                               required>
                    </div>

                    {{-- Department --}}
                    <div class="mb-3">
                        <label class="form-label fw-600">
                            Department <span class="text-danger">*</span>
                        </label>
                        <select name="department_id" class="form-select" required>
                            @foreach($globalDepartments ?? [] as $dept)
                            <option value="{{ $dept->id }}" {{ (int)auth()->user()?->department_id === (int)$dept->id ? 'selected' : '' }}>
                                {{ $dept->name }} {{ $dept->code ? ' ('.$dept->code.')' : '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- General Document Contents --}}
                    <div class="mb-3">
                        <label class="form-label fw-600">
                            Reference <span class="text-danger">*</span>
                        </label>
                        <textarea name="remarks"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Write the general contents, summary, or details of the document being registered…"
                                  required></textarea>
                    </div>

                    {{-- Optional Attachment --}}
                    <div class="mb-3">
                        <label class="form-label fw-600">
                            Physical File Attachment <span class="text-muted fw-normal">(Optional)</span>
                        </label>
                        <input type="file"
                               name="attachment"
                               class="form-control"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png">
                        <div class="form-text text-muted fs-xs">Optional. Max 10 MB. PDF, Word, Excel, PowerPoint, or Image files.</div>
                    </div>

                    <div class="modal-footer border-0 pt-2 px-0 pb-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 fw-600">
                            <i class="fa-solid fa-floppy-disk me-1"></i>Save &amp; Register File
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var overlayFolderSelect = document.getElementById('overlayFolderNumberSelect');
    var overlayFolderNameInp = document.getElementById('overlayFolderNameInput');

    if (overlayFolderSelect && overlayFolderNameInp) {
        overlayFolderSelect.addEventListener('change', function () {
            var selectedOpt = overlayFolderSelect.options[overlayFolderSelect.selectedIndex];
            if (selectedOpt && selectedOpt.dataset && selectedOpt.dataset.name) {
                overlayFolderNameInp.value = selectedOpt.dataset.name;
            }
        });
    }
});
</script>
