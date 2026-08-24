<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Privacy Policy & Data Governance — FileTrack Office Portal">
    <title>Privacy Policy &mdash; FileTrack Office Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/app-custom.css') }}">
</head>

<body class="landing-page">

    {{-- NAVBAR --}}
    <header class="site-header sticky-top">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container py-1">
                <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('welcome') }}">
                    <span class="brand-icon" style="background:transparent;box-shadow:none;">
                        <img src="{{ asset('images/logo.png') }}" alt="Ministry Logo" style="width:64px;height:64px;object-fit:contain;">
                    </span>
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
                        <li class="nav-item"><a class="nav-link" href="{{ route('welcome') }}">Overview</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('help') }}">Help Guide</a></li>
                        <li class="nav-item"><a class="nav-link active" href="{{ route('privacy') }}">Privacy Policy</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('terms') }}">Terms of Use</a></li>
                        <li class="nav-item ms-lg-2"><a class="btn btn-primary btn-sm px-3" href="{{ route('login') }}"><i class="fa-solid fa-right-to-bracket me-1"></i>Login</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main>
        {{-- HERO SECTION --}}
        <section class="hero-section text-center">
            <div class="container" style="max-width:960px;">
                <span class="eyebrow-pill mb-3">
                    <i class="fa-solid fa-shield-halved me-2"></i>Official Information Governance
                </span>
                <h1 class="display-4 fw-800 text-white mb-3">Privacy &amp; Data Governance Policy</h1>
                <p class="hero-copy mx-auto fs-5" style="color:rgba(255,255,255,.82);max-width:740px;">
                    How official government records, user security credentials, department affiliations, and document movement histories are protected.
                </p>
                <div class="mt-3 text-muted small" style="color:rgba(254,243,199,.75) !important;">
                    Last updated: August 2026
                </div>
            </div>
        </section>

        {{-- CONTENT SECTION --}}
        <section class="section-block py-5" style="background:#0b120c;">
            <div class="container" style="max-width:960px;">

                <div class="d-flex flex-column gap-4">

                    {{-- 01: DATA COLLECTION & PURPOSE --}}
                    <div class="dark-page-card p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">01</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-database me-2" style="color:#d4af37;"></i>Data Collection &amp; Purpose
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <p style="color:rgba(255,255,255,.88);line-height:1.8;font-size:1.02rem;" class="mb-0">
                            The FileTrack Office Portal collects official user account details (full name, government email address, department affiliation, assigned designation) and official document metadata (folder numbers, folder names, file titles, movement histories, and dispatch timestamps).
                        </p>
                    </div>

                    {{-- 02: HOW INFORMATION IS USED --}}
                    <div class="dark-page-card p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">02</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-diagram-project me-2" style="color:#d4af37;"></i>How Information Is Used
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <p style="color:rgba(255,255,255,.88);line-height:1.8;font-size:1.02rem;">
                            Information stored in the portal is used exclusively for official government administrative operations:
                        </p>
                        <ul class="lh-lg mb-0" style="color:rgba(255,255,255,.88);font-size:1.02rem;">
                            <li>Managing official file dispatches between government departments and personnel.</li>
                            <li>Tracking action timeframes and enforcing Permanent Secretary return deadlines.</li>
                            <li>Maintaining non-repudiable audit trail logs for transparency and statutory compliance.</li>
                            <li>Dispatching toast and notification alerts regarding file assignments.</li>
                        </ul>
                    </div>

                    {{-- 03: SECURITY & RBAC --}}
                    <div class="dark-page-card p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">03</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-lock me-2" style="color:#d4af37;"></i>Security &amp; Role-Based Access Control
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <p style="color:rgba(255,255,255,.88);line-height:1.8;font-size:1.02rem;" class="mb-0">
                            Data is secured using strict role-based access control (RBAC). Departmental staff can only access files created in or dispatched to their specific department. The Records Department maintains folder-level oversight, while Permanent Secretary reviews are strictly logged and audited.
                        </p>
                    </div>

                    {{-- 04: MANDATORY CREDENTIAL SAFETY --}}
                    <div class="dark-page-card p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">04</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-key me-2" style="color:#d4af37;"></i>Mandatory Credential Safety
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <p style="color:rgba(255,255,255,.88);line-height:1.8;font-size:1.02rem;" class="mb-0">
                            All default user accounts require an immediate password change upon initial login. Session tokens are validated continuously, and unauthorized access attempts trigger instant session invalidation and account lockouts.
                        </p>
                    </div>

                    {{-- 05: DATA RETENTION & ARCHIVAL --}}
                    <div class="dark-page-card p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">05</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-box-archive me-2" style="color:#d4af37;"></i>Data Retention &amp; Archival
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <p style="color:rgba(255,255,255,.88);line-height:1.8;font-size:1.02rem;" class="mb-0">
                            Official file records, dispatch logs, and movement timelines are preserved in accordance with statutory government record retention policies. Completed and archived files remain searchable within authorized departmental archives.
                        </p>
                    </div>

                    {{-- 06: AUDIT & MONITORING --}}
                    <div class="dark-page-card p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">06</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-chart-line me-2" style="color:#d4af37;"></i>Audit &amp; System Monitoring
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <p style="color:rgba(255,255,255,.88);line-height:1.8;font-size:1.02rem;" class="mb-0">
                            Every file creation, transfer, approval, deadline extension, and status change generates an immutable audit record containing timestamp, IP address, user designation, and department details.
                        </p>
                    </div>

                    {{-- 07: CONTACT & GOVERNANCE --}}
                    <div class="dark-page-card p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">07</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-headset me-2" style="color:#d4af37;"></i>Administrative Contact &amp; Governance
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <p style="color:rgba(255,255,255,.88);line-height:1.8;font-size:1.02rem;" class="mb-0">
                            For questions concerning information governance, data access requests, or system security compliance, please contact your Departmental Administrator or the System Administrator.
                        </p>
                    </div>

                </div>

                {{-- ACTION BUTTONS --}}
                <div class="text-center pt-5">
                    <a href="{{ route('help') }}" class="btn btn-outline-light btn-lg me-2 mb-2">
                        <i class="fa-solid fa-circle-question me-2"></i>View Help Guide
                    </a>
                    <a href="{{ route('welcome') }}" class="btn btn-primary btn-lg mb-2">
                        <i class="fa-solid fa-house me-2"></i>Return to Overview
                    </a>
                </div>

            </div>
        </section>
    </main>

    {{-- FOOTER --}}
    <footer class="site-footer">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <div class="footer-brand">
                        <span class="brand-icon" style="background:transparent;box-shadow:none;">
                            <img src="{{ asset('images/logo.png') }}" alt="Ministry Logo" style="width:64px;height:64px;object-fit:contain;">
                        </span>
                        <div>
                            <h5>FileTrack Office</h5>
                            <p class="mb-0">Government File Tracking System</p>
                            <small class="text-muted">Ministry of Sport, Recreation, Arts and Culture</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="footer-links">
                        <a href="{{ route('welcome') }}">Overview</a>
                        <a href="{{ route('public.file.search') }}">Public Search</a>
                        <a href="{{ route('help') }}">Help Guide</a>
                        <a href="{{ route('privacy') }}">Privacy Policy</a>
                        <a href="{{ route('terms') }}">Terms of Use</a>
                        <a href="{{ route('login') }}">Login</a>
                    </div>
                </div>
            </div>
            <div class="footer-note mt-3">&copy; {{ date('Y') }} FileTrack Office Portal. Built for secure public-sector workflows.</div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
