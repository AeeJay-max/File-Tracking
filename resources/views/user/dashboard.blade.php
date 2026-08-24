@extends('layouts.app')
@section('title', 'My Dashboard')
@section('breadcrumb')
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">My Dashboard</h1>
        <div class="page-subtitle">
            {{ auth()->user()->department->name ?? 'No Department' }}
            @if(auth()->user()->designation->name !== '—')
            &mdash; {{ auth()->user()->designation->name }}
            @endif
        </div>
    </div>
    @can('create', App\Models\FileRecord::class)
    <button type="button" class="btn-portal-primary" data-bs-toggle="modal" data-bs-target="#createFileModal">
        <i class="fa-solid fa-plus me-1"></i>New File
    </button>
    @else
    <span class="badge-status badge-pending" style="font-size:.85rem;padding:6px 12px;" title="Only Records Department can create files">
        <i class="fa-solid fa-lock me-1"></i>Files Created by Records Dept
    </span>
    @endcan
</div>

{{-- KPI ROW --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="stat-kpi">
            <div class="stat-kpi-icon green"><i class="fa-solid fa-file-lines"></i></div>
            <div>
                <div class="stat-kpi-label">My Files</div>
                <div class="stat-kpi-value">{{ $totalMyFiles }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-kpi">
            <div class="stat-kpi-icon green"><i class="fa-solid fa-paper-plane"></i></div>
            <div>
                <div class="stat-kpi-label">Sent Files</div>
                <div class="stat-kpi-value">{{ $totalSentFiles }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-kpi">
            <div class="stat-kpi-icon teal"><i class="fa-solid fa-inbox"></i></div>
            <div>
                <div class="stat-kpi-label">Received Files</div>
                <div class="stat-kpi-value">{{ $totalReceivedFiles }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">

    {{-- File Tabs --}}
    <div class="col-lg-8">
        <div class="portal-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <ul class="nav nav-tabs card-header-tabs" id="fileTabs">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-my">
                            <i class="fa-solid fa-file-lines me-1"></i>My Files
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-received">
                            <i class="fa-solid fa-inbox me-1"></i>Received
                            @if($receivedFiles->count() > 0)
                            <span class="badge bg-primary ms-1">{{ $receivedFiles->count() }}</span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-sent">
                            <i class="fa-solid fa-paper-plane me-1"></i>Sent
                        </button>
                    </li>
                </ul>
                <a href="{{ route('files.index') }}" class="btn btn-sm btn-portal-outline">View All</a>
            </div>

            <div class="tab-content">

                {{-- My Files Tab --}}
                <div class="tab-pane fade show active" id="tab-my">
                    <div class="table-responsive">
                        <table class="portal-table">
                            <thead>
                                <tr>
                                    <th>File Number</th>
                                    <th>File Name</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($myFiles as $file)
                                <tr>
                                    <td class="text-muted fs-sm fw-700">{{ $file->file_number }}</td>
                                    <td>{{ $file->file_name }}</td>
                                    <td>@include('partials.status-badge', ['status' => $file->status])</td>
                                    <td class="text-muted fs-sm">{{ $file->created_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('files.show', $file->uuid) }}" class="btn btn-sm btn-outline-primary" title="View"><i class="fa-solid fa-eye"></i></a>
                                            @if($file->status !== 'archived' && (int)$file->current_user_id === auth()->id())
                                            <a href="{{ route('files.transfer.create', $file->uuid) }}" class="btn btn-sm btn-outline-secondary" title="Transfer"><i class="fa-solid fa-right-left"></i></a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-file-circle-question"></i>No files yet.</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Received Tab --}}
                <div class="tab-pane fade" id="tab-received">
                    <div class="table-responsive">
                        <table class="portal-table">
                            <thead>
                                <tr>
                                    <th>File</th>
                                    <th>Sent By</th>
                                    <th>Department</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($receivedFiles as $t)
                                <tr>
                                    <td>
                                        <div class="fw-700">{{ $t->file->file_name ?? 'N/A' }}</div>
                                        <div class="text-muted fs-sm">{{ $t->file->file_number ?? '' }}</div>
                                    </td>
                                    <td>{{ $t->sender->name ?? 'System' }}</td>
                                    <td class="text-muted">{{ $t->file->department->name ?? '—' }}</td>
                                    <td class="text-muted fs-sm">{{ $t->created_at->format('d M Y') }}</td>
                                    <td>
                                        @if($t->file)
                                        <a href="{{ route('files.show', $t->file->uuid) }}" class="btn btn-sm btn-outline-primary" title="View"><i class="fa-solid fa-eye"></i></a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-inbox"></i>No files received yet.</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Sent Tab --}}
                <div class="tab-pane fade" id="tab-sent">
                    <div class="table-responsive">
                        <table class="portal-table">
                            <thead>
                                <tr>
                                    <th>File</th>
                                    <th>Sent To</th>
                                    <th>Remarks</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sentFiles as $t)
                                <tr>
                                    <td>
                                        <div class="fw-700">{{ $t->file->file_name ?? 'N/A' }}</div>
                                        <div class="text-muted fs-sm">{{ $t->file->file_number ?? '' }}</div>
                                    </td>
                                    <td>{{ $t->receiver->name ?? 'Department' }}</td>
                                    <td class="text-muted fs-sm">{{ $t->remarks ?? '—' }}</td>
                                    <td class="text-muted fs-sm">{{ $t->created_at->format('d M Y') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4"><div class="empty-state"><i class="fa-solid fa-paper-plane"></i>No files sent yet.</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Right column --}}
    <div class="col-lg-4">

        {{-- Unread Notifications --}}
        <div class="portal-card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-bell me-2 text-primary"></i>Notifications</span>
                <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-portal-outline">All</a>
            </div>
            <div class="card-body p-0">
                @forelse($unreadNotifications as $n)
                <div class="d-flex gap-3 px-3 py-2 border-bottom notif-unread">
                    <div class="mt-1 text-primary"><i class="fa-solid fa-circle-dot fs-sm"></i></div>
                    <div>
                        <div style="font-size:.845rem;font-weight:600;">{{ $n->data['message'] ?? 'Notification' }}</div>
                        <div class="text-muted fs-sm">{{ $n->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                @empty
                <div class="empty-state py-4"><i class="fa-solid fa-bell-slash"></i>No new notifications.</div>
                @endforelse
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="portal-card">
            <div class="card-header"><i class="fa-solid fa-route me-2 text-primary"></i>Recent Activity</div>
            <div class="card-body p-0">
                @forelse($recentActivity as $item)
                @php
                    $fileUuid = $item->file?->uuid;
                    $cardUrl  = $fileUuid ? route('files.show', $fileUuid) : '#';
                    $isDept   = $item->fromDept && $item->toDept
                                && (int)$item->fromDept->id !== (int)$item->toDept->id;
                @endphp
                <a href="{{ $cardUrl }}" class="rfm-card {{ $isDept ? 'rfm-card-dept' : '' }}"
                   style="text-decoration:none;color:inherit;">
                    <div class="rfm-left">
                        <div class="rfm-icon rfm-icon-{{ $item->action }}">
                            @if($item->action === 'created')       <i class="fa-solid fa-file-circle-plus"></i>
                            @elseif($item->action === 'transferred')<i class="fa-solid fa-paper-plane"></i>
                            @else                                   <i class="fa-solid fa-circle-dot"></i>
                            @endif
                        </div>
                    </div>
                    <div class="rfm-body">
                        <div class="rfm-file">
                            <span class="rfm-file-num">{{ $item->file?->file_number ?? 'N/A' }}</span>
                            <span class="rfm-file-name">{{ Str::limit($item->file?->file_name ?? '', 30) }}</span>
                        </div>
                        <div class="rfm-flow">
                            <span class="rfm-person">
                                <i class="fa-solid fa-user fa-xs me-1"></i>{{ $item->fromUser?->name ?? 'System' }}
                            </span>
                            <span class="rfm-arrow"><i class="fa-solid fa-arrow-right fa-xs"></i></span>
                            <span class="rfm-person {{ $isDept ? 'rfm-dept-node' : '' }}">
                                @if($isDept)
                                <i class="fa-solid fa-building-columns fa-xs me-1"></i>{{ $item->toDept?->name ?? '—' }}
                                @else
                                <i class="fa-solid fa-user fa-xs me-1"></i>{{ $item->toUser?->name ?? '—' }}
                                @endif
                            </span>
                        </div>
                        @if($item->remarks)
                        <div class="rfm-remarks">{{ Str::limit($item->remarks, 50) }}</div>
                        @endif
                    </div>
                    <div class="rfm-right">
                        @include('partials.action-badge', ['action' => $item->action])
                        <div class="rfm-time">{{ $item->created_at->diffForHumans() }}</div>
                    </div>
                </a>
                @empty
                <div class="empty-state py-4"><i class="fa-solid fa-inbox"></i>No activity yet.</div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
