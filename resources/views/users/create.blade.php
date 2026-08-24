@extends('layouts.app')
@section('title', 'Create User Account')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('users.index') }}">User Management</a></li>
<li class="breadcrumb-item active">Create User</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Create User Account</h1>
        <div class="page-subtitle">Super Admin can create Departmental Admins and Users.</div>
    </div>
    <a href="{{ route('users.index') }}" class="btn-portal-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<div class="portal-form-card">
    <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" class="portal-form">
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <div class="alert alert-info d-flex align-items-center gap-2" role="alert">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>This form creates a <strong>Departmental Admin</strong> or <strong>User</strong> account. Default password is <code>Password@123</code>. The user will be prompted to change it on first login.</span>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Full Name <span class="required-star">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Email Address <span class="required-star">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Role <span class="required-star">*</span></label>
                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                    <option value="admin" {{ old('role', 'admin') === 'admin' ? 'selected' : '' }}>Departmental Admin</option>
                    <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User (Standard Staff)</option>
                </select>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Department <span class="required-star">*</span></label>
                <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                    <option value="">Select Department</option>
                    @foreach($departments as $dept)
                    @php
                    $isRec = strtoupper($dept->code) === 'REC' || \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($dept->name), 'record');
                    @endphp
                    <option value="{{ $dept->id }}"
                            data-is-records="{{ $isRec ? '1' : '0' }}"
                            {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }} {{ $isRec ? '(Records)' : '' }}
                    </option>
                    @endforeach
                </select>
                @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Designation</label>
                <select name="designation_id" class="form-select" id="designationSelect">
                    <option value="">Select Designation</option>
                    @foreach($designations as $des)
                    <option value="{{ $des->id }}" data-department-id="{{ $des->department_id }}"
                        {{ old('designation_id') == $des->id ? 'selected' : '' }}>{{ $des->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Profile Photo</label>
                <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/jpeg,image/png">
                @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn-portal-primary"><i class="fa-solid fa-floppy-disk"></i> Create User Account</button>
            <a href="{{ route('users.index') }}" class="btn-portal-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var roleSelect = document.querySelector('select[name="role"]');
        var departmentSelect = document.querySelector('select[name="department_id"]');
        var designationSelect = document.getElementById('designationSelect');

        function syncDesignations() {
            var departmentId = departmentSelect.value;
            Array.from(designationSelect.options).forEach(function(opt) {
                if (opt.value === '') { opt.hidden = false; return; }
                opt.hidden = departmentId && opt.dataset.departmentId !== departmentId;
            });
            if (designationSelect.selectedOptions[0] && designationSelect.selectedOptions[0].hidden) {
                designationSelect.value = '';
            }
        }

        function filterDepartments() {
            var isUserRole = roleSelect && roleSelect.value === 'user';
            Array.from(departmentSelect.options).forEach(function(opt) {
                if (opt.value === '') return;
                var isRecords = opt.dataset.isRecords === '1';
                if (isUserRole && isRecords) {
                    opt.hidden = true;
                    opt.disabled = true;
                    if (departmentSelect.value === opt.value) {
                        departmentSelect.value = '';
                    }
                } else {
                    opt.hidden = false;
                    opt.disabled = false;
                }
            });
            syncDesignations();
        }

        if (departmentSelect && designationSelect) {
            departmentSelect.addEventListener('change', syncDesignations);
        }

        if (roleSelect && departmentSelect) {
            roleSelect.addEventListener('change', filterDepartments);
            filterDepartments();
        }
    });
</script>
@endpush
