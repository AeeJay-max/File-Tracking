<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'FileTrack Office Portal') }} &mdash; @yield('title', 'Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    @stack('styles')
</head>

<body class="portal-body">

    @auth
    @php
    $role = auth()->user()->role;
    $isSuper = $role === 'super_admin';
    $isAdmin = $role === 'admin';
    $isUser = $role === 'user';
    $dashRoute = $isSuper ? 'super_admin.dashboard' : ($isAdmin ? 'admin.dashboard' : 'user.dashboard');
    $unreadCount = auth()->user()->notifications()->whereNull('read_at')->count();
    $latestNotifications = auth()->user()->notifications()->latest()->limit(15)->get()
        ->map(fn($notification) => \App\Support\NotificationPresenter::present($notification));
    @endphp

    <!-- ================================================================
     SIDEBAR
================================================================ -->
    <div class="portal-sidebar" id="portalSidebar">
        <div class="sidebar-brand">
            <div class="brand-icon-wrap">
                <img src="{{ asset('images/logo.png') }}" alt="Ministry Logo" class="sidebar-logo-img">
            </div>
            <div class="brand-text">
                <span class="brand-name">FileTrack</span>
                <span class="brand-sub">Office Portal</span>
            </div>
        </div>

        <nav class="sidebar-nav">

            {{-- ── COMMON ─────────────────────────────────────── --}}
            <div class="nav-section-label">Main</div>

            <a href="{{ route($dashRoute) }}"
                class="sidebar-link {{ request()->routeIs($dashRoute) || request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-house"></i><span>Dashboard</span>
            </a>

            <a href="{{ route('files.index') }}"
                class="sidebar-link {{ request()->routeIs('files.index','files.show','files.create') ? 'active' : '' }}">
                <i class="fa-solid fa-file-lines"></i>
                <span>{{ $isUser ? 'My Files' : 'Files' }}</span>
            </a>

            <a href="{{ route('notifications.index') }}"
                class="sidebar-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                <i class="fa-solid fa-bell"></i><span>Notifications</span>
                @if($unreadCount > 0)
                <span class="sidebar-badge" id="sb-notif-count">{{ $unreadCount }}</span>
                @else
                <span class="sidebar-badge d-none" id="sb-notif-count"></span>
                @endif
            </a>

            {{-- ── ADMIN SECTION ───────────────────────────────── --}}
            @if($isAdmin || $isSuper)
            <div class="nav-section-label mt-2">Administration</div>

            <a href="{{ route('admin.files') }}"
                class="sidebar-link {{ request()->routeIs('admin.files*') ? 'active' : '' }}">
                <i class="fa-solid fa-folder-open"></i><span>All Files</span>
            </a>

            <a href="{{ route('admin.transfers') }}"
                class="sidebar-link {{ request()->routeIs('admin.transfers') ? 'active' : '' }}">
                <i class="fa-solid fa-right-left"></i><span>Transfer History</span>
            </a>

            {{-- Admin: User Management (dept users only) --}}
            @if($isAdmin)
            <a href="{{ route('admin.users.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i><span>User Management</span>
            </a>
            @endif

            <a href="{{ route('admin.designations.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.designations.*') ? 'active' : '' }}">
                <i class="fa-solid fa-id-badge"></i><span>Designations</span>
            </a>

            @if($isSuper)
            <a href="{{ route('admin.backup.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.backup.*') ? 'active' : '' }}">
                <i class="fa-solid fa-database"></i><span>Backup</span>
            </a>
            @endif
            @endif

            {{-- ── SUPER ADMIN ONLY ────────────────────────────── --}}
            @if($isSuper)
            <div class="nav-section-label mt-2">System</div>

            <a href="{{ route('departments.index') }}"
                class="sidebar-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                <i class="fa-solid fa-building-columns"></i><span>Departments</span>
            </a>

            <a href="{{ route('users.index') }}"
                class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users-gear"></i><span>User &amp; Officer Management</span>
            </a>
            @endif

         

            {{-- ── ACCOUNT ─────────────────────────────────────── --}}
            <div class="nav-section-label mt-2">Account</div>

            <a href="{{ route('profile.edit') }}"
                class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="fa-solid fa-user-pen"></i><span>Profile</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" class="sidebar-logout-form">
                @csrf
                <button type="submit" class="sidebar-link sidebar-logout">
                    <i class="fa-solid fa-right-from-bracket"></i><span>Logout</span>
                </button>
            </form>

        </nav>
    </div>
    {{-- END SIDEBAR --}}

    <div class="mobile-backdrop" id="sidebarBackdrop"></div>

    <!-- ================================================================
     MAIN AREA
================================================================ -->
    <div class="portal-main" id="portalMain">

        <!-- TOP NAVBAR -->
        <header class="portal-topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle-btn" id="sidebarToggle" title="Toggle Sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="topbar-context d-none d-sm-flex">
                    <span class="topbar-context-label">Government File Tracking</span>
                    <span class="topbar-context-sub">Official workflow portal</span>
                </div>
                <nav aria-label="breadcrumb" class="d-none d-lg-block">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route($dashRoute) }}">Home</a>
                        </li>
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>

            <div class="topbar-right">
                {{-- Notification Bell --}}
                <div class="dropdown me-2">
                    <button class="topbar-icon-btn" data-bs-toggle="dropdown" id="topbar-bell-btn" aria-label="Notifications">
                        <i class="fa-solid fa-bell"></i>
                        @if($unreadCount > 0)
                        <span class="topbar-badge" id="topbar-notif-badge">{{ $unreadCount }}</span>
                        @else
                        <span class="topbar-badge d-none" id="topbar-notif-badge"></span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notif-dropdown p-0" id="notif-dropdown-menu">
                        <div class="notif-header d-flex align-items-center gap-2 px-3 py-2">
                            <i class="fa-solid fa-bell text-primary"></i>
                            <strong>Notifications</strong>
                        </div>
                        <div class="notif-body" id="notif-dropdown-body">
                            @forelse($latestNotifications as $n)
                            <a href="{{ $n['url'] }}" class="notif-item {{ $n['is_unread'] ? 'notif-unread' : '' }}" data-notification-id="{{ $n['id'] }}" data-unread="{{ $n['is_unread'] ? '1' : '0' }}">
                                <span class="notif-icon notif-color-{{ $n['color'] }}"><i class="fa-solid fa-{{ $n['icon'] }}"></i></span>
                                <span class="notif-content">
                                    <span class="notif-title">{{ $n['title'] }}</span>
                                    <span class="notif-msg">{{ $n['message'] }}</span>
                                    <small class="text-muted">{{ $n['relative_time'] }}</small>
                                </span>
                            </a>
                            @empty
                            <div class="notif-item text-muted text-center">No notifications</div>
                            @endforelse
                        </div>
                        <div class="notif-footer text-center py-2">
                            <a href="{{ route('notifications.index') }}" class="small">View all notifications</a>
                        </div>
                    </div>
                </div>

                {{-- User Dropdown --}}
                <div class="dropdown">
                    <button class="topbar-user-btn" data-bs-toggle="dropdown">

                        @if(auth()->user()->photo)
                        <img
                            src="{{ auth()->user()->photo_url }}"
                            alt="Profile"
                            class="topbar-avatar-img"
                            onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <div class="topbar-avatar" style="display:none;">
                            {{ auth()->user()->initials }}
                        </div>
                        @else
                        <div class="topbar-avatar">
                            {{ auth()->user()->initials }}
                        </div>
                        @endif



                        <div class="d-none d-md-block text-start">
                            <div class="topbar-user-name">{{ auth()->user()->name }}</div>
                            <div class="topbar-user-role">{{ match($role) { 'super_admin' => 'Director', 'admin' => 'Departmental Admin', default => 'User' } }}</div>
                        </div>
                        <i class="fa-solid fa-chevron-down ms-1 small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="fa-solid fa-user-pen me-2"></i>Profile
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>
        {{-- END TOPBAR --}}

        <!-- PAGE CONTENT -->
        <main class="portal-content">

            {{-- ── Impersonation Banner ──────────────────────────── --}}
            @if(session('impersonator_id'))
            <div class="impersonation-banner d-flex align-items-center justify-content-between flex-wrap gap-2"
                 role="alert" aria-live="assertive">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-user-secret fa-lg"></i>
                    <span>
                        You are impersonating
                        <strong>{{ auth()->user()->name }}</strong>
                        <span class="ms-1 badge bg-white text-dark">{{ ucfirst(auth()->user()->role) }}</span>
                    </span>
                </div>
                <form method="POST" action="{{ route('impersonation.stop') }}" class="mb-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-light fw-700">
                        <i class="fa-solid fa-right-from-bracket me-1"></i>Stop Impersonating
                    </button>
                </form>
            </div>
            @endif

            {{-- ── Force Password Change Warning ──────────────────── --}}
            @auth
            @if(auth()->user()->must_change_password)
            <div class="alert alert-warning d-flex align-items-center gap-2 portal-alert" role="alert">
                <i class="fa-solid fa-key fa-lg"></i>
                <div>
                    <strong>Action Required:</strong> You must change your password before using the system.
                    <a href="{{ route('profile.edit') }}" class="alert-link ms-1">Change Password Now</a>
                </div>
            </div>
            @endif
            @endauth

            {{-- Global Flash Alerts --}}
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show portal-alert" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show portal-alert" role="alert">
                <i class="fa-solid fa-circle-xmark me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show portal-alert" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @yield('content')
        </main>

        <!-- FOOTER -->
        <footer class="portal-footer">
            <span>&copy; {{ date('Y') }} FileTrack Office Portal &mdash; Government File Tracking System</span>
        </footer>
    </div>
    {{-- END MAIN --}}

    @endauth

    <script type="application/json" id="latest-notifications-data">{{ $latestNotifications->values()->toJson() }}</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ── Sidebar toggle ────────────────────────────────────────────────
        const sidebar = document.getElementById('portalSidebar');
        const mainArea = document.getElementById('portalMain');
        const toggleBtn = document.getElementById('sidebarToggle');
        const backdrop = document.getElementById('sidebarBackdrop');

        function isMobileView() {
            return window.matchMedia('(max-width: 991px)').matches;
        }

        function syncSidebar() {
            if (!sidebar || !mainArea || !toggleBtn) return;

            if (isMobileView()) {
                sidebar.classList.remove('collapsed');
                mainArea.classList.remove('expanded');
                sidebar.classList.remove('mobile-open');
                if (backdrop) {
                    backdrop.classList.remove('show');
                }
                document.body.classList.remove('sidebar-open');
            } else {
                const shouldCollapse = localStorage.getItem('sidebarCollapsed') === 'true';
                sidebar.classList.toggle('collapsed', shouldCollapse);
                mainArea.classList.toggle('expanded', shouldCollapse);
                sidebar.classList.remove('mobile-open');
                if (backdrop) {
                    backdrop.classList.remove('show');
                }
                document.body.classList.remove('sidebar-open');
            }
        }

        if (sidebar && mainArea && toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                if (isMobileView()) {
                    sidebar.classList.toggle('mobile-open');
                    const isOpen = sidebar.classList.contains('mobile-open');
                    if (backdrop) {
                        backdrop.classList.toggle('show', isOpen);
                    }
                    document.body.classList.toggle('sidebar-open', isOpen);
                } else {
                    sidebar.classList.toggle('collapsed');
                    mainArea.classList.toggle('expanded');
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                }
            });

            if (backdrop) {
                backdrop.addEventListener('click', () => {
                    sidebar.classList.remove('mobile-open');
                    backdrop.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                });
            }

            window.addEventListener('resize', syncSidebar);
            syncSidebar();
        }

        // ── Notification polling — Page Visibility aware ─────────────────
        (function() {
            const topBadge    = document.getElementById('topbar-notif-badge');
            const sbCount     = document.getElementById('sb-notif-count');
            const bellBtn     = document.getElementById('topbar-bell-btn');
            const dropdownBody = document.getElementById('notif-dropdown-body');
            const latestSeed   = document.getElementById('latest-notifications-data');

            const POLL_MS  = 10000; // poll every 10 s
            const FIRST_MS = 1500;  // first poll 1.5 s after load
            const csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

            // Seed lastCount from server-rendered badge so we don't play sound on page load
            let lastCount = parseInt(topBadge ? (topBadge.textContent || '0') : '0', 10) || 0;
            let pollTimer = null;
            let pollInFlight = false;
            let latestNotifications = [];
            if (latestSeed) {
                try {
                    latestNotifications = JSON.parse(latestSeed.textContent || '[]');
                } catch (e) {
                    latestNotifications = [];
                }
            }

            // ── Sound ─────────────────────────────────────────────────────
            // Use Audio() object — not the hidden <audio> element.
            // Browser autoplay policy: we must wait for a user gesture before playing.
            var notifSound = null;
            var soundReady = false;
            const notifSoundUrl = "{{ asset('sounds/notification.mp3') }}";

            function initSound() {
                if (notifSound) return; // already created
                notifSound = new Audio(notifSoundUrl);
                notifSound.preload = 'auto';
                soundReady = true;
            }

            // Unlock audio on first real user interaction
            ['click', 'keydown', 'touchstart', 'pointerdown'].forEach(function(ev) {
                document.addEventListener(ev, function handler() {
                    initSound();
                    document.removeEventListener(ev, handler);
                }, { once: true, passive: true });
            });

            function playSound() {
                if (!soundReady || !notifSound) return;
                notifSound.currentTime = 0;
                notifSound.play().catch(function() {});
            }

            // ── Badge ─────────────────────────────────────────────────────
            function setBadge(el, n) {
                if (!el) return;
                if (n > 0) {
                    el.textContent = n;
                    el.classList.remove('d-none');
                    el.classList.add('badge-bounce');
                    setTimeout(function() { el.classList.remove('badge-bounce'); }, 650);
                } else {
                    el.textContent = '';
                    el.classList.add('d-none');
                }
            }

            // ── HTML escaping ─────────────────────────────────────────────
            function escapeHtml(value) {
                var div = document.createElement('div');
                div.textContent = (value == null) ? '' : String(value);
                return div.innerHTML;
            }

            // ── Render dropdown list ──────────────────────────────────────
            function renderNotifications(items) {
                if (!dropdownBody) return;
                latestNotifications = items || [];

                if (!latestNotifications.length) {
                    dropdownBody.innerHTML = '<div class="notif-item text-muted text-center">No notifications</div>';
                    return;
                }

                dropdownBody.innerHTML = latestNotifications.map(function(item) {
                    return '<a href="' + escapeHtml(item.url || '#') +
                        '" class="notif-item' + (item.is_unread ? ' notif-unread' : '') + '"' +
                        ' data-notification-id="' + escapeHtml(item.id) + '"' +
                        ' data-unread="' + (item.is_unread ? '1' : '0') + '">' +
                        '<span class="notif-icon notif-color-' + escapeHtml(item.color || 'gray') + '">' +
                        '<i class="fa-solid fa-' + escapeHtml(item.icon || 'bell') + '"></i></span>' +
                        '<span class="notif-content">' +
                        '<span class="notif-title">' + escapeHtml(item.title || 'Notification') + '</span>' +
                        '<span class="notif-msg">' + escapeHtml(item.message || '') + '</span>' +
                        '<small class="text-muted">' + escapeHtml(item.relative_time || '') + '</small>' +
                        '</span></a>';
                }).join('');
            }

            // ── Get IDs of currently visible unread items ─────────────────
            function unreadVisibleIds() {
                if (!dropdownBody) return [];
                return Array.from(
                    dropdownBody.querySelectorAll('[data-notification-id][data-unread="1"]')
                ).slice(0, 15).map(function(el) { return el.dataset.notificationId; });
            }

            // ── Mark visible items read (called when dropdown opens) ──────
            function markVisibleAsRead() {
                var ids = unreadVisibleIds();
                if (!ids.length) return;

                // Optimistic UI update — update local state + re-render immediately
                latestNotifications = latestNotifications.map(function(item) {
                    if (ids.indexOf(item.id) !== -1) {
                        item.is_unread = false;
                        item.read_at   = new Date().toISOString();
                    }
                    return item;
                });
                renderNotifications(latestNotifications);

                // Update badge from local estimate first
                var localUnread = latestNotifications.filter(function(n) { return n.is_unread; }).length;
                lastCount = localUnread;
                setBadge(topBadge, localUnread);
                setBadge(sbCount,  localUnread);

                // Persist to server; use server response to sync exact count
                fetch('{{ route("notifications.readVisible") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ ids: ids })
                })
                .then(function(r) { return r.ok ? r.json() : null; })
                .then(function(data) {
                    if (!data) return;
                    // Sync badge with authoritative server count
                    lastCount = data.unread_count || 0;
                    setBadge(topBadge, lastCount);
                    setBadge(sbCount,  lastCount);
                })
                .catch(function() {});
            }

            // ── Poll for new notifications ────────────────────────────────
            function scheduleNextPoll() {
                if (pollTimer) clearTimeout(pollTimer);
                pollTimer = setTimeout(poll, POLL_MS);
            }

            function poll() {
                if (document.hidden || pollInFlight) return;

                pollInFlight = true;

                fetch('{{ route("notifications.poll") }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf
                    },
                    credentials: 'same-origin'
                })
                .then(function(r) { return r.ok ? r.json() : null; })
                .then(function(data) {
                    if (!data) return;

                    var newCount = data.unread_count || 0;

                    // Play sound ONLY when unread count genuinely increased
                    if (newCount > lastCount) {
                        playSound();
                    }

                    lastCount = newCount;
                    setBadge(topBadge, newCount);
                    setBadge(sbCount,  newCount);
                    renderNotifications(data.notifications || []);

                    // If dropdown is currently open, auto-mark newly arrived items
                    if (bellBtn && bellBtn.getAttribute('aria-expanded') === 'true') {
                        markVisibleAsRead();
                    }
                })
                .catch(function() {})
                .finally(function() {
                    pollInFlight = false;
                    if (!document.hidden) {
                        scheduleNextPoll();
                    }
                });
            }

            function stopPolling() {
                clearTimeout(pollTimer);
                pollTimer = null;
            }

            // ── Page visibility: pause polling when tab is hidden ─────────
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    stopPolling();
                } else {
                    poll();
                }
            });

            // ── Bell button: mark visible as read when dropdown opens ─────
            if (bellBtn) {
                bellBtn.addEventListener('shown.bs.dropdown', function() {
                    markVisibleAsRead();
                });
            }

            // ── Notification item click: mark single item read, navigate ──
            if (dropdownBody) {
                dropdownBody.addEventListener('click', function(e) {
                    var link = e.target.closest('a[data-notification-id]');
                    if (!link) return;

                    var url      = link.getAttribute('href');
                    var isUnread = link.dataset.unread === '1';
                    var id       = link.dataset.notificationId;

                    if (!isUnread) return; // already read — normal <a> navigation

                    e.preventDefault();

                    // Optimistic update
                    link.dataset.unread = '0';
                    link.classList.remove('notif-unread');
                    lastCount = Math.max(0, lastCount - 1);
                    setBadge(topBadge, lastCount);
                    setBadge(sbCount,  lastCount);

                    fetch('{{ route("notifications.readVisible") }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrf
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ ids: [id] })
                    })
                    .then(function(r) { return r.ok ? r.json() : null; })
                    .then(function(data) {
                        if (data) {
                            lastCount = data.unread_count || 0;
                            setBadge(topBadge, lastCount);
                            setBadge(sbCount,  lastCount);
                        }
                    })
                    .catch(function() {})
                    .finally(function() {
                        if (url && url !== '#') window.location.href = url;
                    });
                });
            }

            // ── Initial poll after short delay ────────────────────────────
            setTimeout(function() {
                poll();
            }, FIRST_MS);

        })();
    </script>
    @stack('scripts')
</body>

</html>
