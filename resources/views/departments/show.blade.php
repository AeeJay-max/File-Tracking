@extends('layouts.app')
@section('title', 'Department Details')
@section('content')
<div class="page-header">
    <h1 class="page-title">{{ $department->name }}</h1>
    <a href="{{ route('departments.index') }}" class="btn-portal-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>
<div class="portal-form-card">
    <div class="row g-3">
        <div class="col-md-6"><div class="text-muted fs-sm mb-1">Name</div><div class="fw-700">{{ $department->name }}</div></div>
        <div class="col-md-6"><div class="text-muted fs-sm mb-1">Code</div><code>{{ $department->code }}</code></div>
        <div class="col-md-6"><div class="text-muted fs-sm mb-1">Status</div>@include('partials.status-badge', ['status' => $department->is_active ? 'active' : 'archived'])</div>
        <div class="col-md-6"><div class="text-muted fs-sm mb-1">Created</div>{{ $department->created_at->format('d M Y') }}</div>
    </div>
</div>
@endsection
