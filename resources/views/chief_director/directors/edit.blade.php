@extends('layouts.app')

@section('title', 'Edit Director — ' . $director->name)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('chief_director.dashboard') }}">Chief Director</a></li>
<li class="breadcrumb-item"><a href="{{ route('chief_director.directors.index') }}">Directors</a></li>
<li class="breadcrumb-item active">Edit Director</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Director Account</h1>
        <div class="page-subtitle">{{ $director->display_title }}</div>
    </div>
    <div>
        <a href="{{ route('chief_director.directors.index') }}" class="btn-portal-outline">
            <i class="fa-solid fa-arrow-left me-1"></i>Cancel
        </a>
    </div>
</div>

<div class="portal-card max-w-3xl">
    <div class="card-header">
        <i class="fa-solid fa-user-pen me-2 text-primary"></i>Director Information Form
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('chief_director.directors.update', $director->uuid) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label fw-600">Full Name</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $director->name) }}" required>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label fw-600">Email Address</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $director->email) }}" required>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label fw-600">Phone Number</label>
                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $director->phone) }}">
                @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-600">Department</label>
                <input type="text" class="form-control bg-light" value="{{ $director->department->name ?? 'Unassigned' }}" readonly disabled>
                <small class="text-muted">Department structure is managed by Super Admin.</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-600">Display Title</label>
                <input type="text" class="form-control bg-light" value="{{ $director->display_title }}" readonly disabled>
            </div>

            <div class="mb-4">
                <label for="is_active" class="form-label fw-600">Account Status</label>
                <select name="is_active" id="is_active" class="form-select @error('is_active') is-invalid @enderror">
                    <option value="1" {{ old('is_active', $director->is_active) ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ ! old('is_active', $director->is_active) ? 'selected' : '' }}>Inactive / Deactivated</option>
                </select>
                @error('is_active')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="{{ route('chief_director.directors.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn-portal-primary"><i class="fa-solid fa-save me-1"></i>Update Director Account</button>
            </div>
        </form>
    </div>
</div>
@endsection
