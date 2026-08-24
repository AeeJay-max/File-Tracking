<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Terms of Use — FileTrack Office Portal">
    <title>Terms of Use &mdash; FileTrack Office Portal</title>

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
                        <li class="nav-item"><a class="nav-link" href="{{ route('privacy') }}">Privacy Policy</a></li>
                        <li class="nav-item"><a class="nav-link active" href="{{ route('terms') }}">Terms of Use</a></li>
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
                    <i class="fa-solid fa-scale-balanced me-2"></i>Official Portal Terms
                </span>
                <h1 class="display-4 fw-800 text-white mb-3">Terms of Use</h1>
                <p class="hero-copy mx-auto fs-5" style="color:rgba(255,255,255,.82);max-width:740px;">
                    Conditions and responsibilities governing authorized access, file tracking operations, and record handling within the FileTrack Office Portal.
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

                    {{-- 01: AUTHORIZED USE --}}
                    <div class="dark-page-card p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">01</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-user-check me-2" style="color:#d4af37;"></i>Authorized Use
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <p style="color:rgba(255,255,255,.88);line-height:1.8;font-size:1.02rem;" class="mb-0">
                            This portal is authorized for use strictly by designated government department personnel and official administrators. Unauthorized access, attempt to bypass access controls, or misuse of portal functionality is strictly prohibited and subject to legal prosecution under public sector statutes.
                        </p>
                    </div>

                    {{-- 02: USER RESPONSIBILITIES --}}
                    <div class="dark-page-card p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">02</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-clipboard-check me-2" style="color:#d4af37;"></i>User Responsibilities
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <p style="color:rgba(255,255,255,.88);line-height:1.8;font-size:1.02rem;" class="mb-0">
                            Users are responsible for maintaining the confidentiality of their credentials and for all actions taken under their assigned account. Users must ensure that official file numbers, folder numbers, summaries, and dispatch instructions are entered accurately.
                        </p>
                    </div>

                    {{-- 03: DATA INTEGRITY --}}
                    <div class="dark-page-card p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">03</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-shield-cat me-2" style="color:#d4af37;"></i>Data Integrity &amp; Record Accuracy
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <p style="color:rgba(255,255,255,.88);line-height:1.8;font-size:1.02rem;" class="mb-0">
                            Users must not alter, delete, or manipulate official file records, movement timelines, or return deadlines without proper statutory authorization. Every file modification is logged and permanently linked to the performing account.
                        </p>
                    </div>

                    {{-- 04: MONITORING --}}
                    <div class="dark-page-card p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">04</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-eye me-2" style="color:#d4af37;"></i>System Activity &amp; Audit Monitoring
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <p style="color:rgba(255,255,255,.88);line-height:1.8;font-size:1.02rem;" class="mb-0">
                            All activity within this system is continuously monitored and logged for security, compliance, and audit purposes. System logs include user IDs, timestamps, IP addresses, and specific file transactions performed.
                        </p>
                    </div>

                    {{-- 05: ACCOUNT SECURITY --}}
                    <div class="dark-page-card p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">05</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-key me-2" style="color:#d4af37;"></i>Account Security &amp; Credential Protection
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <p style="color:rgba(255,255,255,.88);line-height:1.8;font-size:1.02rem;" class="mb-0">
                            Sharing user credentials or allowing unauthorized personnel to perform dispatches under your session is strictly forbidden. Accounts requiring mandatory password update must complete the update immediately upon prompt.
                        </p>
                    </div>

                    {{-- 06: PROHIBITED ACTIVITIES --}}
                    <div class="dark-page-card p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">06</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-ban me-2" style="color:#d4af37;"></i>Prohibited Activities
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <ul class="lh-lg mb-0" style="color:rgba(255,255,255,.88);font-size:1.02rem;">
                            <li>Attempting to access records outside authorized departmental scope without valid dispatch.</li>
                            <li>Uploading malformed or malicious attachments.</li>
                            <li>Interfering with system availability, notification polling, or audit mechanisms.</li>
                        </ul>
                    </div>

                    {{-- 07: ACCESS RESTRICTIONS --}}
                    <div class="dark-page-card p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">07</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-user-slash me-2" style="color:#d4af37;"></i>Access Restrictions &amp; Revocation
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <p style="color:rgba(255,255,255,.88);line-height:1.8;font-size:1.02rem;" class="mb-0">
                            The Ministry reserves the right to suspend or revoke user access privileges at any time upon departmental reassignment, breach of portal terms, or security directive.
                        </p>
                    </div>

                    {{-- 08: POLICY UPDATES --}}
                    <div class="dark-page-card p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">08</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-rotate me-2" style="color:#d4af37;"></i>Policy Updates &amp; Governing Standards
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <p style="color:rgba(255,255,255,.88);line-height:1.8;font-size:1.02rem;" class="mb-0">
                            These terms may be updated periodically to align with revised government administrative directives. Continued use of the portal signifies acceptance of updated governance standards.
                        </p>
                    </div>

                </div>

                {{-- ACTION BUTTONS --}}
                <div class="text-center pt-5">
                    <a href="{{ route('privacy') }}" class="btn btn-outline-light btn-lg me-2 mb-2">
                        <i class="fa-solid fa-shield-halved me-2"></i>Privacy Policy
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
