<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Government-grade file tracking and workflow management system.">
    <title>FileTrack Office Portal &mdash; Government File Tracking System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/app-custom.css') }}">
</head>

<body class="landing-page">
    @php
        $featureCards = [
            ['icon' => 'fa-file-circle-plus', 'title' => 'File Management', 'text' => 'Create, review, and track official documents with a complete audit trail.'],
            ['icon' => 'fa-building-user', 'title' => 'Department Control', 'text' => 'Keep teams, desks, and responsibilities organized with clear ownership.'],
            ['icon' => 'fa-user-gear', 'title' => 'User Administration', 'text' => 'Assign roles and manage access without exposing sensitive workflow data.'],
            ['icon' => 'fa-right-left', 'title' => 'Fast Transfers', 'text' => 'Route files instantly between departments and users with status visibility.'],
            ['icon' => 'fa-timeline', 'title' => 'Journey Tracking', 'text' => 'Inspect every movement from creation to delivery in one clean timeline.'],
            ['icon' => 'fa-magnifying-glass', 'title' => 'Public Search', 'text' => 'Let citizens and staff verify file status using a simple file number search.'],
        ];

        $workflowSteps = [
            ['number' => '01', 'title' => 'Register', 'text' => 'Capture the file with metadata, department ownership, and remarks.'],
            ['number' => '02', 'title' => 'Route', 'text' => 'Transfer the file to the next responsible user or department.'],
            ['number' => '03', 'title' => 'Assign', 'text' => 'Department admins assign incoming files to the right staff member.'],
            ['number' => '04', 'title' => 'Audit', 'text' => 'Every action is recorded in a searchable, immutable movement history.'],
        ];

        $securityItems = [
            'Role-based access for super admin, admin, and user workflows',
            'Impersonation banner and password-change protections remain intact',
            'Public search exposes only safe, non-sensitive file status information',
            'Audit-friendly movement history for every file transfer and assignment',
        ];

        $techStack = ['Laravel', 'Bootstrap 5', 'Blade', 'Eloquent ORM', 'MySQL', 'Font Awesome', 'PHP 8.2+'];

        $previewTiles = [
            ['title' => 'Dashboard', 'text' => 'Role-aware summary cards and recent movements.', 'icon' => 'fa-chart-column'],
            ['title' => 'File Detail', 'text' => 'Timeline, remarks, attachment, and transfer controls.', 'icon' => 'fa-folder-open'],
            ['title' => 'Notifications', 'text' => 'Unread updates with instant badge refresh.', 'icon' => 'fa-bell'],
        ];
    @endphp

    <header class="site-header sticky-top">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container py-1">
                <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('welcome') }}">
                    <span class="brand-icon" style="background:transparent;box-shadow:none;"><img src="{{ asset('images/logo.png') }}" alt="Ministry Logo" style="width:64px;height:64px;object-fit:contain;"></span>
                    <span>
                        <span class="brand-title">FileTrack Office</span>
                        <span class="brand-subtitle">Government File Tracking System</span>
                    </span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#siteNav" aria-controls="siteNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end" id="siteNav">
                    <ul class="navbar-nav align-items-lg-center gap-lg-2 mt-3 mt-lg-0">
                        <li class="nav-item"><a class="nav-link" href="#overview">Overview</a></li>
                        <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                        <li class="nav-item"><a class="nav-link" href="#workflow">Workflow</a></li>
                        <li class="nav-item"><a class="nav-link" href="#security">Security</a></li>
                        <li class="nav-item"><a class="nav-link" href="#preview">Preview</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('public.file.search') }}">Public Search</a></li>
                        <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                        <li class="nav-item ms-lg-2"><a class="btn btn-outline-light btn-sm px-3" href="{{ route('public.file.search') }}"><i class="fa-solid fa-magnifying-glass me-1"></i>Search Files</a></li>
                        <li class="nav-item ms-lg-2"><a class="btn btn-primary btn-sm px-3" href="{{ route('login') }}"><i class="fa-solid fa-right-to-bracket me-1"></i>Login</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero-section" id="home">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-7 reveal" data-reveal>
                        <span class="eyebrow-pill"><i class="fa-solid fa-shield-halved me-2"></i>Official workflow portal</span>
                        <h1>Track every file with clarity, security, and speed.</h1>
                        <p class="hero-copy">FileTrack Office keeps government file movement visible from the moment a record is created until it reaches the right department, user, or public search result.</p>
                        <div class="hero-actions">
                            <a href="{{ route('public.file.search') }}" class="btn btn-primary btn-lg"><i class="fa-solid fa-magnifying-glass me-2"></i>Search a File</a>
                            <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg"><i class="fa-solid fa-right-to-bracket me-2"></i>Open Portal</a>
                        </div>
                        <div class="hero-stats row g-3 mt-4">
                            <div class="col-6 col-lg-3">
                                <div class="hero-stat">
                                    <small>Departments</small>
                                    <strong>{{ $stats['departments'] }}</strong>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3">
                                <div class="hero-stat">
                                    <small>Users</small>
                                    <strong>{{ $stats['users'] }}</strong>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3">
                                <div class="hero-stat">
                                    <small>Files</small>
                                    <strong>{{ $stats['files'] }}</strong>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3">
                                <div class="hero-stat">
                                    <small>Transfers</small>
                                    <strong>{{ $stats['transfers'] }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5 reveal" data-reveal>
                        <div class="glass-card hero-panel">
                            <div class="panel-header">
                                <div>
                                    <span class="panel-kicker">Live command center</span>
                                    <h2>Fast, accountable, audit-ready</h2>
                                </div>
                                <span class="status-pill"><i class="fa-solid fa-circle text-success me-1"></i>Online</span>
                            </div>
                            <div class="panel-grid">
                                <div class="panel-metric">
                                    <small>Pending assignments</small>
                                    <strong>{{ max($stats['transfers'] - 1, 0) }}</strong>
                                </div>
                                <div class="panel-metric">
                                    <small>Public search</small>
                                    <strong>Instant</strong>
                                </div>
                                <div class="panel-metric">
                                    <small>Audit trail</small>
                                    <strong>Always on</strong>
                                </div>
                            </div>
                            <div class="mini-timeline mt-4">
                                <div class="mini-timeline-item">
                                    <span class="mini-dot created"></span>
                                    <div>
                                        <strong>Register file</strong>
                                        <small>Create a new record with number, title, and remarks.</small>
                                    </div>
                                </div>
                                <div class="mini-timeline-item">
                                    <span class="mini-dot transferred"></span>
                                    <div>
                                        <strong>Transfer or assign</strong>
                                        <small>Move ownership to the next department or user.</small>
                                    </div>
                                </div>
                                <div class="mini-timeline-item">
                                    <span class="mini-dot delivered"></span>
                                    <div>
                                        <strong>Track delivery</strong>
                                        <small>Inspect the entire file journey from one timeline.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-block" id="overview">
            <div class="container">
                <div class="section-head reveal" data-reveal>
                    <span class="eyebrow">Project Introduction</span>
                    <h2>Designed for formal office operations</h2>
                    <p>FileTrack Office centralizes government file handling into a clean, role-aware workflow that is easier to follow, faster to operate, and simpler to audit.</p>
                </div>
                <div class="row g-4">
                    <div class="col-md-4 reveal" data-reveal>
                        <div class="feature-card intro-card h-100">
                            <i class="fa-solid fa-clipboard-check"></i>
                            <h5>Controlled Process</h5>
                            <p>Keep every file in a defined path from creation to completion.</p>
                        </div>
                    </div>
                    <div class="col-md-4 reveal" data-reveal>
                        <div class="feature-card intro-card h-100">
                            <i class="fa-solid fa-circle-user"></i>
                            <h5>Role Awareness</h5>
                            <p>Super admins, admins, and users each get the right tools and views.</p>
                        </div>
                    </div>
                    <div class="col-md-4 reveal" data-reveal>
                        <div class="feature-card intro-card h-100">
                            <i class="fa-solid fa-lock"></i>
                            <h5>Public Safety</h5>
                            <p>Public search reveals only the minimum data needed to verify a file.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-block section-alt" id="features">
            <div class="container">
                <div class="section-head text-center reveal" data-reveal>
                    <span class="eyebrow">Features</span>
                    <h2>Everything needed for file lifecycle control</h2>
                </div>
                <div class="row g-4">
                    @foreach($featureCards as $card)
                    <div class="col-md-6 col-lg-4 reveal" data-reveal>
                        <div class="feature-card h-100">
                            <div class="feature-icon"><i class="fa-solid {{ $card['icon'] }}"></i></div>
                            <h5>{{ $card['title'] }}</h5>
                            <p>{{ $card['text'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section-block" id="workflow">
            <div class="container">
                <div class="section-head text-center reveal" data-reveal>
                    <span class="eyebrow">Workflow Illustration</span>
                    <h2>How the process works</h2>
                </div>
                <div class="workflow-rail reveal" data-reveal>
                    @foreach($workflowSteps as $step)
                    <div class="workflow-step">
                        <span>{{ $step['number'] }}</span>
                        <h5>{{ $step['title'] }}</h5>
                        <p>{{ $step['text'] }}</p>
                    </div>
                    @if(! $loop->last)
                    <div class="workflow-connector">
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section-block section-alt" id="journey">
            <div class="container">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-5 reveal" data-reveal>
                        <span class="eyebrow">File Journey Preview</span>
                        <h2>Follow each movement without losing context</h2>
                        <p class="section-copy">The timeline preview mirrors the production file journey so teams can see creation, transfer, assignment, and delivery in a single view.</p>
                        <div class="journey-summary">
                            <div>
                                <small>Live records</small>
                                <strong>{{ $stats['files'] }}</strong>
                            </div>
                            <div>
                                <small>Transfers logged</small>
                                <strong>{{ $stats['transfers'] }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7 reveal" data-reveal>
                        <div class="glass-card journey-card">
                            <div class="journey-card-head">
                                <div>
                                    <span class="panel-kicker">Sample file journey</span>
                                    <h3>FILE-1001 / Finance Requisition</h3>
                                </div>
                                <span class="badge-soft">Updated moments ago</span>
                            </div>
                            <div class="journey-track">
                                <div class="journey-node created">
                                    <span>Created</span>
                                    <p>Registered by the originating department.</p>
                                </div>
                                <div class="journey-node transferred">
                                    <span>Transferred</span>
                                    <p>Routed to the next responsible office.</p>
                                </div>
                                <div class="journey-node assigned">
                                    <span>Assigned</span>
                                    <p>Department admin assigned the file to a user.</p>
                                </div>
                                <div class="journey-node delivered">
                                    <span>Delivered</span>
                                    <p>Receipt and audit history are recorded automatically.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-block" id="security">
            <div class="container">
                <div class="row g-4 align-items-stretch">
                    <div class="col-lg-6 reveal" data-reveal>
                        <div class="glass-card security-card h-100">
                            <span class="eyebrow">Security</span>
                            <h2>Government-friendly controls that protect the workflow</h2>
                            <ul class="checklist mt-4 mb-0">
                                @foreach($securityItems as $item)
                                <li><i class="fa-solid fa-circle-check"></i><span>{{ $item }}</span></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-6 reveal" data-reveal>
                        <div class="security-grid h-100">
                            <div class="security-tile"><i class="fa-solid fa-shield-halved"></i><strong>Protected Access</strong><span>Authenticated routes remain gated.</span></div>
                            <div class="security-tile"><i class="fa-solid fa-user-secret"></i><strong>Impersonation Safety</strong><span>Banner and stop action stay visible.</span></div>
                            <div class="security-tile"><i class="fa-solid fa-bell"></i><strong>Notification Hygiene</strong><span>Unread state is updated immediately.</span></div>
                            <div class="security-tile"><i class="fa-solid fa-file-shield"></i><strong>Audit Trails</strong><span>File movements remain fully traceable.</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-block section-alt" id="stack">
            <div class="container">
                <div class="section-head text-center reveal" data-reveal>
                    <span class="eyebrow">Technology Stack</span>
                    <h2>Built on a reliable, maintainable stack</h2>
                </div>
                <div class="stack-wrap reveal" data-reveal>
                    @foreach($techStack as $tech)
                    <span class="stack-chip">{{ $tech }}</span>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section-block" id="preview">
            <div class="container">
                <div class="section-head text-center reveal" data-reveal>
                    <span class="eyebrow">Screenshots / Preview</span>
                    <h2>Interface preview</h2>
                </div>
                <div class="row g-4">
                    @foreach($previewTiles as $tile)
                    <div class="col-md-4 reveal" data-reveal>
                        <div class="preview-card h-100">
                            <div class="preview-icon"><i class="fa-solid {{ $tile['icon'] }}"></i></div>
                            <h5>{{ $tile['title'] }}</h5>
                            <p>{{ $tile['text'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section-block section-contact" id="contact">
            <div class="container">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-7 reveal" data-reveal>
                        <span class="eyebrow">Contact</span>
                        <h2>Need help getting started?</h2>
                        <p class="section-copy mb-0">Use the public search page for file verification, or open the portal to continue with authenticated file management and assignments.</p>
                    </div>
                    <div class="col-lg-5 reveal" data-reveal>
                        <div class="contact-card">
                            <a href="{{ route('help') }}" class="btn btn-light w-100 mb-3"><i class="fa-solid fa-circle-question me-2"></i>Help Center</a>
                            <a href="{{ route('public.file.search') }}" class="btn btn-outline-light w-100 mb-3"><i class="fa-solid fa-magnifying-glass me-2"></i>Public File Search</a>
                            <a href="{{ route('login') }}" class="btn btn-primary w-100"><i class="fa-solid fa-right-to-bracket me-2"></i>Login to Portal</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <div class="footer-brand">
                        <span class="brand-icon" style="background:transparent;box-shadow:none;"><img src="{{ asset('images/logo.png') }}" alt="Ministry Logo" style="width:64px;height:64px;object-fit:contain;"></span>
                        <div>
                            <h5>FileTrack Office</h5>
                            <p>Government-grade file tracking and departmental workflow management.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="footer-links">
                        <a href="#overview">Overview</a>
                        <a href="#features">Features</a>
                        <a href="#security">Security</a>
                        <a href="{{ route('public.file.search') }}">Public Search</a>
                    </div>
                </div>
            </div>
            <div class="footer-note">&copy; {{ date('Y') }} FileTrack Office Portal. Built for secure public-sector workflows.</div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            const revealItems = document.querySelectorAll('[data-reveal]');

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries, io) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            io.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.18 });

                revealItems.forEach((item) => observer.observe(item));
            } else {
                revealItems.forEach((item) => item.classList.add('is-visible'));
            }
        })();
    </script>
</body>

</html>