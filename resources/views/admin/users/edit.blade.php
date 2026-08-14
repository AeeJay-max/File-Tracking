@extends('layouts.app')
@section('title', 'Edit User')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit User</h1>
        <div class="page-subtitle">Update user details for {{ $user->name }}</div>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn-portal-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<div class="portal-form-card">
    <form method="POST" action="{{ route('admin.users.update', $user->uuid) }}" enctype="multipart/form-data" class="portal-form">
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
                <label class="form-label">Email Address <span class="required-star">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $user->email) }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Designation</label>
                <select name="designation_id" class="form-select">
                    <option value="">Select Designation</option>
                    @foreach($designations as $des)
                    <option value="{{ $des->id }}" {{ $user->designation_id == $des->id ? 'selected' : '' }}>{{ $des->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn-portal-primary"><i class="fa-solid fa-floppy-disk"></i> Update User</button>
            <a href="{{ route('admin.users.index') }}" class="btn-portal-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection