@extends('layouts.app')
@section('title', 'Edit Admin Account')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('users.index') }}">Admin Management</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Admin Account</h1>
        <div class="page-subtitle">Update details for {{ $user->name }}</div>
    </div>
    <a href="{{ route('users.index') }}" class="btn-portal-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<div class="portal-form-card">
    <form method="POST" action="{{ route('users.update', $user->uuid) }}" enctype="multipart/form-data" class="portal-form">
        @csrf @method('PUT')
        <div class="row g-3">
            @if($user->photo_url)
            <div class="col-12">
                <div class="mb-3 d-flex align-items-center gap-3">
                    <img src="{{ $user->photo_url }}" alt="{{ $user->name }}" class="rounded-circle" style="width:72px;height:72px;object-fit:cover;">
                    <div class="text-muted">Current profile photo for {{ $user->name }}</div>
                </div>
            </div>
            @endif
            <div class="col-md-6">
                <label class="form-label">Full Name <span class="required-star">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $user->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Email <span class="required-star">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $user->email) }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">New Password <span class="text-muted">(leave blank to keep current)</span></label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Role</label>
                <input type="text" class="form-control bg-light" value="Admin" readonly>
                <div class="form-text text-muted"><i class="fa-solid fa-lock me-1"></i>Role cannot be changed here.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">None</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ $user->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Designation</label>
                <select name="designation_id" class="form-select" id="designationSelect">
                    <option value="">None</option>
                    @foreach($designations as $des)
                    <option value="{{ $des->id }}" data-department-id="{{ $des->department_id }}"
                        {{ $user->designation_id == $des->id ? 'selected' : '' }}>{{ $des->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $user->contact_number) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Profile Photo</label>
                <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/jpeg,image/png">
                @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn-portal-primary"><i class="fa-solid fa-floppy-disk"></i> Update Admin</button>
            <a href="{{ route('users.index') }}" class="btn-portal-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var departmentSelect = document.querySelector('select[name="department_id"]');
        var designationSelect = document.getElementById('designationSelect');

        function syncDesignations() {
            if (!departmentSelect || !designationSelect) return;
            var departmentId = departmentSelect.value;
            Array.from(designationSelect.options).forEach(function(opt) {
                if (opt.value === '') { opt.hidden = false; return; }
                opt.hidden = departmentId && opt.dataset.departmentId !== departmentId;
            });
            if (designationSelect.selectedOptions[0] && designationSelect.selectedOptions[0].hidden) {
                designationSelect.value = '';
            }
        }

        if (departmentSelect && designationSelect) {
            departmentSelect.addEventListener('change', syncDesignations);
            syncDesignations();
        }
    });
</script>
@endpush
