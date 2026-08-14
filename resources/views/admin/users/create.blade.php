@extends('layouts.app')
@section('title', 'Add User')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
<li class="breadcrumb-item active">Add User</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Add New User</h1>
        <div class="page-subtitle">Create a new user in your department</div>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn-portal-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<div class="portal-form-card">
    <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" class="portal-form">
        @csrf

        <div class="row g-3">
            <div class="col-12">
                <div class="alert alert-info d-flex align-items-center gap-2 mb-2">
                    <i class="fa-solid fa-key"></i>
                    <span>Default password is <code>Password@123</code>. The user will be prompted to change it on first login.</span>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Full Name <span class="required-star">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name') }}" placeholder="Enter full name" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" class="form-control"
                    value="{{ old('contact_number') }}" placeholder="+1 234 567 8900">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email Address <span class="required-star">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" placeholder="user@example.com" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Designation <span class="required-star">*</span></label>
                <select name="designation_id" class="form-select @error('designation_id') is-invalid @enderror" required>
                    <option value="">Select Designation</option>
                    @foreach($designations as $des)
                    <option value="{{ $des->id }}" {{ old('designation_id') == $des->id ? 'selected' : '' }}>{{ $des->name }}</option>
                    @endforeach
                </select>
                @error('designation_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Profile Photo</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="can_create_file" value="1"
                        id="canCreateFile" {{ old('can_create_file') ? 'checked' : '' }}>
                    <label class="form-check-label fw-600" for="canCreateFile">Allow this user to create files</label>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn-portal-primary"><i class="fa-solid fa-floppy-disk"></i> Save User</button>
            <a href="{{ route('admin.users.index') }}" class="btn-portal-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
