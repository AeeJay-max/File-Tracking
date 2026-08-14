@extends('layouts.app')
@section('title', 'Add Designation')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.designations.index') }}">Designations</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Add Designation</h1>
    <a href="{{ route('admin.designations.index') }}" class="btn-portal-outline">
        <i class="fa-solid fa-arrow-left"></i> Back
    </a>
</div>

<div class="portal-form-card">
    <form method="POST" action="{{ route('admin.designations.store') }}" class="portal-form">
        @csrf
        <div class="row g-3">

            {{-- Department: required for Super Admin, pre-filled for Admin --}}
            @if(auth()->user()->role === 'super_admin')
            <div class="col-md-6">
                <label class="form-label">Department <span class="required-star">*</span></label>
                <select name="department_id"
                        class="form-select @error('department_id') is-invalid @enderror"
                        required>
                    <option value="">Select Department</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                    @endforeach
                </select>
                @error('department_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            @else
            <div class="col-md-6">
                <label class="form-label">Department</label>
                <input type="text" class="form-control"
                    value="{{ auth()->user()->department->name ?? 'N/A' }}" readonly>
            </div>
            @endif

            <div class="col-md-6">
                <label class="form-label">Designation Name <span class="required-star">*</span></label>
                <input type="text" name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name') }}" required>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn-portal-primary">
                <i class="fa-solid fa-floppy-disk me-1"></i> Save Designation
            </button>
            <a href="{{ route('admin.designations.index') }}" class="btn-portal-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
