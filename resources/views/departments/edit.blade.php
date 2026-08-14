@extends('layouts.app')
@section('title', 'Edit Department')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departments</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Department</h1>
        <div class="page-subtitle">Update details for {{ $department->name }}</div>
    </div>
    <a href="{{ route('departments.index') }}" class="btn-portal-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<div class="portal-form-card">
    <form method="POST" action="{{ route('departments.update', $department->uuid) }}" class="portal-form">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Department Name <span class="required-star">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $department->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Department Code <span class="required-star">*</span></label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                    value="{{ old('code', $department->code) }}" required>
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" {{ $department->is_active ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$department->is_active ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn-portal-primary"><i class="fa-solid fa-floppy-disk"></i> Update Department</button>
            <a href="{{ route('departments.index') }}" class="btn-portal-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
