@extends('layouts.app')
@section('title', 'Incoming Department Files')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Incoming Files</li>
@endsection

@section('content')
@php $deptName = auth()->user()->department->name ?? 'Your Department'; @endphp

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fa-solid fa-inbox me-2 text-warning"></i>Incoming Department Files
        </h1>
        <div class="page-subtitle">
            {{ $deptName }} &mdash; Files transferred to your department awaiting user assignment
        </div>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn-portal-outline">
        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

{{-- Success / Error flash --}}
@if(session('success'))
<div class="alert alert-success d-flex align-items-center gap-2 mb-4">
    <i class="fa-solid fa-circle-check"></i>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
    <i class="fa-solid fa-circle-xmark"></i>
    {{ session('error') }}
</div>
@endif

@if($pendingFiles->isEmpty())
{{-- ── Empty state ─────────────────────────────────────── --}}
<div class="portal-card">
    <div class="card-body">
        <div class="empty-state py-5">
            <i class="fa-solid fa-circle-check fa-2x text-success mb-3" style="opacity:.6;"></i>
            <p class="fw-600 mb-1">All files assigned</p>
            <p class="text-muted fs-sm">No files are waiting for assignment in {{ $deptName }}.</p>
        </div>
    </div>
</div>

@else
{{-- ── Files table ──────────────────────────────────────── --}}
<div class="portal-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="fa-solid fa-inbox me-2 text-warning"></i>
            Files Awaiting Assignment
            <span class="badge bg-warning text-dark ms-2">{{ $pendingFiles->count() }}</span>
        </span>
        <span class="text-muted fs-sm">Assign each file to a user in {{ $deptName }}</span>
    </div>

    <div class="table-responsive">
        <table class="portal-table">
            <thead>
                <tr>
                    <th>File Number</th>
                    <th>File Name</th>
                    <th>Transferred From</th>
                    <th>Received</th>
                    <th style="min-width:220px;">Assign To</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingFiles as $file)
                @php
                    $lastMove = $file->movements->first();
                    $fromUser = $lastMove?->fromUser;
                    $fromDept = $lastMove?->fromDept;
                @endphp
                <tr>
                    <td>
                        <a href="{{ route('admin.files.timeline', $file->uuid) }}"
                           class="fw-700 text-portal-primary text-decoration-none">
                            {{ $file->file_number }}
                        </a>
                    </td>
                    <td>
                        <div class="fw-600">{{ $file->file_name }}</div>
                        @if($file->remarks)
                        <div class="text-muted fs-sm text-truncate" style="max-width:220px;"
                             title="{{ $file->remarks }}">
                            <i class="fa-solid fa-comment-dots fa-xs me-1"></i>{{ Str::limit($file->remarks, 60) }}
                        </div>
                        @endif
                    </td>
                    <td class="text-muted fs-sm">
                        @if($fromUser)
                        <div class="fw-600 text-dark">{{ $fromUser->name }}</div>
                        @endif
                        @if($fromDept)
                        <div><i class="fa-solid fa-building-columns fa-xs me-1"></i>{{ $fromDept->name }}</div>
                        @endif
                    </td>
                    <td class="text-muted fs-sm">
                        {{ $file->updated_at->format('d M Y') }}<br>
                        <span class="text-muted" style="font-size:.75rem;">{{ $file->updated_at->format('h:i A') }}</span>
                    </td>
                    <td>
                        <form action="{{ route('admin.files.pending.assign', $file->uuid) }}"
                              method="POST"
                            class="d-grid gap-2 d-sm-flex align-items-sm-center"
                              id="assign-form-{{ $file->id }}">
                            @csrf
                            <select name="user_id"
                                    class="form-select form-select-sm"
                                    style="min-width:160px;"
                                    required
                                    aria-label="Select user to assign {{ $file->file_number }}">
                                <option value="" disabled selected>— Select user —</option>
                                @foreach($deptUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="btn btn-sm btn-warning fw-600"
                                    style="white-space:nowrap;">
                                <i class="fa-solid fa-user-plus me-1"></i>Assign
                            </button>
                        </form>
                    </td>
                    <td>
                        <a href="{{ route('admin.files.timeline', $file->uuid) }}"
                           class="btn btn-sm btn-outline-primary"
                           title="View Timeline">
                            <i class="fa-solid fa-timeline"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
