@extends('layouts.app')
@section('title', 'Transfer History')

@section('breadcrumb')
<li class="breadcrumb-item active">Transfer History</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Transfer History</h1>
        <div class="page-subtitle">
            @if(auth()->user()->role === 'super_admin')
                All department transfers — system-wide view
            @else
                {{ auth()->user()->department->name ?? 'Department' }} — all transfers involving your department
            @endif
        </div>
    </div>
</div>

<div class="portal-table-wrap mb-0">

    {{-- Filter Bar --}}
    <form action="{{ route('admin.transfers') }}" method="GET" class="table-toolbar">
        <input type="text" name="search" class="form-control" style="max-width:220px;min-width:160px;"
            placeholder="File name or number..." value="{{ request('search') }}">

        @if(auth()->user()->role === 'super_admin')
        <select name="department_id" class="form-select" style="max-width:200px;">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
            <option value="{{ $dept->uuid }}" {{ request('department_id') == $dept->uuid ? 'selected' : '' }}>
                {{ $dept->name }}
            </option>
            @endforeach
        </select>
        @endif

        <input type="date" name="from_date" class="form-control" style="max-width:140px;" value="{{ request('from_date') }}">
        <input type="date" name="to_date"   class="form-control" style="max-width:140px;" value="{{ request('to_date') }}">

        <button type="submit" class="btn btn-primary btn-sm px-3">
            <i class="fa-solid fa-magnifying-glass me-1"></i>Filter
        </button>
        <a href="{{ route('admin.transfers') }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
    </form>

    <div class="table-responsive">
        <table class="portal-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>File</th>
                    <th>Sender</th>
                    <th>Receiver</th>
                    <th>From Dept.</th>
                    <th>To Dept.</th>
                    <th>Date &amp; Time</th>
                    <th>Remarks</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($transfers as $i => $move)
                <tr>
                    <td class="text-muted">{{ $transfers->firstItem() + $i }}</td>
                    <td>
                        <div class="fw-700">{{ $move->file->file_name ?? 'N/A' }}</div>
                        <div class="text-muted fs-sm">{{ $move->file->file_number ?? '' }}</div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($move->fromUser)
                                @if($move->fromUser->photo_url)
                                <img src="{{ $move->fromUser->photo_url }}" alt="{{ $move->fromUser->name }}"
                                     style="width:24px;height:24px;border-radius:50%;object-fit:cover;">
                                @else
                                <div style="width:24px;height:24px;border-radius:50%;background:#e8f5e9;color:#005a2b;
                                            display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:700;flex-shrink:0;">
                                    {{ $move->fromUser->initials }}
                                </div>
                                @endif
                                <span class="fs-sm">{{ $move->fromUser->name }}</span>
                            @else
                            <span class="text-muted fs-sm">—</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($move->toUser)
                                @if($move->toUser->photo_url)
                                <img src="{{ $move->toUser->photo_url }}" alt="{{ $move->toUser->name }}"
                                     style="width:24px;height:24px;border-radius:50%;object-fit:cover;">
                                @else
                                <div style="width:24px;height:24px;border-radius:50%;background:#d1fae5;color:#059669;
                                            display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:700;flex-shrink:0;">
                                    {{ $move->toUser->initials }}
                                </div>
                                @endif
                                <span class="fs-sm">{{ $move->toUser->name }}</span>
                            @else
                            <span class="text-muted fs-sm">—</span>
                            @endif
                        </div>
                    </td>
                    <td class="text-muted fs-sm">{{ $move->fromDept->name ?? '—' }}</td>
                    <td class="text-muted fs-sm">{{ $move->toDept->name ?? '—' }}</td>
                    <td class="text-muted fs-sm">{{ $move->created_at->format('d M Y, h:i A') }}</td>
                    <td class="text-muted fs-sm" style="max-width:180px;">
                        {{ $move->remarks ? Str::limit($move->remarks, 60) : '—' }}
                    </td>
                    <td>
                        @if($move->file)
                        <a href="{{ route('files.timeline', $move->file->uuid) }}"
                           class="btn btn-sm btn-outline-primary" title="View Timeline">
                            <i class="fa-solid fa-timeline"></i>
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <i class="fa-solid fa-right-left"></i>No transfers found.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transfers->hasPages())
    <div class="px-4 py-3 border-top">{{ $transfers->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
