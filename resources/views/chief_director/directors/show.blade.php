@extends('layouts.app')

@section('title', 'Director Profile — ' . $director->name)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('chief_director.dashboard') }}">Chief Director</a></li>
<li class="breadcrumb-item"><a href="{{ route('chief_director.directors.index') }}">Directors</a></li>
<li class="breadcrumb-item active">{{ $director->name }}</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $director->name }}</h1>
        <div class="page-subtitle text-portal-primary fw-600"><i class="fa-solid fa-user-tie me-1"></i>{{ $director->display_title }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('chief_director.directors.index') }}" class="btn-portal-outline">
            <i class="fa-solid fa-arrow-left me-1"></i>Back to Directors
        </a>
        <a href="{{ route('chief_director.directors.edit', $director->uuid) }}" class="btn btn-secondary">
            <i class="fa-solid fa-pen-to-square me-1"></i>Edit Director
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="portal-card text-center p-4">
            <div class="mb-3 mx-auto" style="width:80px;height:80px;border-radius:50%;background:#e8f5e9;color:#005a2b;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:2rem;">
                {{ $director->initials }}
            </div>
            <h4 class="fw-700 mb-1">{{ $director->name }}</h4>
            <div class="badge bg-primary mb-3 p-2 px-3">{{ $director->display_title }}</div>
            <div>
                @if($director->is_active)
                    <span class="badge bg-success px-3 py-2"><i class="fa-solid fa-circle-check me-1"></i>Active Account</span>
                @else
                    <span class="badge bg-danger px-3 py-2"><i class="fa-solid fa-user-slash me-1"></i>Inactive Account</span>
                @endif
            </div>


            <form action="{{ route('chief_director.directors.toggle-status', $director->uuid) }}" method="POST" class="mt-4">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn w-100 {{ $director->is_active ? 'btn-outline-danger' : 'btn-success' }}">
                    <i class="fa-solid {{ $director->is_active ? 'fa-user-slash me-1' : 'fa-user-check me-1' }}"></i>
                    {{ $director->is_active ? 'Deactivate Director Account' : 'Activate Director Account' }}
                </button>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="portal-card h-100">
            <div class="card-header">
                <i class="fa-solid fa-id-card me-2 text-primary"></i>Director Information
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">Full Name</div>
                        <div class="fw-700">{{ $director->name }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">Display Title</div>
                        <div class="fw-700 text-indigo" style="color:#4f46e5;">{{ $director->display_title }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">Department</div>
                        <div class="fw-600">{{ $director->department->name ?? 'N/A' }} ({{ $director->department->code ?? 'N/A' }})</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">System Role</div>
                        <div><span class="badge bg-secondary">Departmental Admin (role = admin)</span></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">Email Address</div>
                        <div>{{ $director->email }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">Phone Number</div>
                        <div>{{ $director->phone ?? 'Not provided' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">Department Current Files</div>
                        <div class="fw-700 text-primary">{{ $departmentFileCount }} Files</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted fs-sm mb-1">Account Created</div>
                        <div>{{ $director->created_at->format('d M Y, h:i A') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
