@extends('layouts.app')
@section('title', 'Edit File')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('files.index') }}">Files</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit File</h1>
        <div class="page-subtitle">Update the file record and attachment</div>
    </div>
    <a href="{{ route('files.show', $file->uuid) }}" class="btn-portal-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<div class="portal-form-card">
    <form action="{{ route('files.update', $file->uuid) }}" method="POST" enctype="multipart/form-data" class="portal-form">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Department</label>
            <input type="text" class="form-control bg-light" value="{{ $file->department->name ?? 'N/A' }}" readonly>
            <div class="form-text text-muted"><i class="fa-solid fa-lock me-1"></i>Department is set during file creation.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">File Name <span class="required-star">*</span></label>
            <input type="text" name="file_name" class="form-control @error('file_name') is-invalid @enderror"
                value="{{ old('file_name', $file->file_name) }}" placeholder="Enter file name or subject" required>
            @error('file_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-4">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror"
                rows="3" placeholder="Optional remarks or notes">{{ old('remarks', $file->remarks) }}</textarea>
            @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-4">
            <label class="form-label">Upload Document</label>
            <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror"
                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png">
            @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @if($file->attachment_path)
            <div class="form-text mt-2">
                Current: <strong>{{ $file->attachment_name }}</strong>
            </div>
            @endif
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn-portal-primary"><i class="fa-solid fa-floppy-disk"></i> Update File</button>
            <a href="{{ route('files.show', $file->uuid) }}" class="btn-portal-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
