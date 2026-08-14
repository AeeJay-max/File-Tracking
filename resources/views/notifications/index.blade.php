@extends('layouts.app')
@section('title', 'Notifications')

@section('breadcrumb')
<li class="breadcrumb-item active">Notifications</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Notifications</h1>
        <div class="page-subtitle">Your latest alerts and updates</div>
    </div>
</div>

<div class="portal-card">
    <div class="card-body p-0">
        @forelse($notifications as $notification)
        @php($n = \App\Support\NotificationPresenter::present($notification))
        <a href="{{ $n['url'] }}"
           class="notification-row {{ $n['is_unread'] ? 'notif-unread' : '' }}">
            <span class="notif-icon notif-color-{{ $n['color'] }}">
                <i class="fa-solid fa-{{ $n['icon'] }}"></i>
            </span>

            <span class="notif-content">
                <span class="notif-title">{{ $n['title'] }}</span>
                <span class="notif-msg">{{ $n['message'] }}</span>
                <small class="text-muted">{{ $n['relative_time'] }}</small>
            </span>

            @if($n['is_unread'])
            <span class="notif-status-dot" aria-label="Unread"></span>
            @endif
        </a>
        @empty
        <div class="empty-state py-5">
            <i class="fa-solid fa-bell-slash"></i>
            No notifications yet.
        </div>
        @endforelse
    </div>
</div>

@if($notifications->hasPages())
<div class="mt-3">
    {{ $notifications->links() }}
</div>
@endif
@endsection
