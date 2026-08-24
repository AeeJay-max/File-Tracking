<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Official User Documentation & Step-by-Step Guide — FileTrack Office Portal">
    <title>Help &amp; User Guide &mdash; FileTrack Office Portal</title>

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
                        <li class="nav-item"><a class="nav-link active" href="{{ route('help') }}">Help Guide</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('privacy') }}">Privacy Policy</a></li>
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
                    <i class="fa-solid fa-graduation-cap me-2"></i>Official User Documentation
                </span>
                <h1 class="display-4 fw-800 text-white mb-3">FileTrack System User Guide</h1>
                <p class="hero-copy mx-auto fs-5" style="color:rgba(255,255,255,.82);max-width:760px;">
                    Comprehensive step-by-step instructions for portal access, folder management, file registration, department dispatches, return deadlines, and audit tracking.
                </p>
                <div class="mt-3 text-muted small" style="color:rgba(254,243,199,.75) !important;">
                    Last updated: August 2026
                </div>
            </div>
        </section>

        {{-- CONTENT SECTION --}}
        <section class="section-block py-5" style="background:#0b120c;">
            <div class="container" style="max-width:1040px;">

                {{-- QUICK NAVIGATION INDEX --}}
                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <a href="#step-login" class="dark-page-card text-decoration-none d-block p-3">
                            <div class="d-flex align-items-center gap-3">
                                <span class="dark-page-badge">01</span>
                                <div>
                                    <h6 class="fw-700 text-white mb-0">1. Logging In</h6>
                                    <small style="color:rgba(255,255,255,.65);">Portal access &amp; setup</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="#step-folders" class="dark-page-card text-decoration-none d-block p-3">
                            <div class="d-flex align-items-center gap-3">
                                <span class="dark-page-badge">02</span>
                                <div>
                                    <h6 class="fw-700 text-white mb-0">2. Folder Arrangement</h6>
                                    <small style="color:rgba(255,255,255,.65);">Folder numbers &amp; creation</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="#step-file-creation" class="dark-page-card text-decoration-none d-block p-3">
                            <div class="d-flex align-items-center gap-3">
                                <span class="dark-page-badge">03</span>
                                <div>
                                    <h6 class="fw-700 text-white mb-0">3. Registering Files</h6>
                                    <small style="color:rgba(255,255,255,.65);">Auto-filling folder names</small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                {{-- INSTRUCTIONAL CARDS --}}
                <div class="d-flex flex-column gap-4">

                    {{-- 01: LOGGING IN --}}
                    <div class="dark-page-card p-4 p-md-5" id="step-login">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">01</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-right-to-bracket me-2" style="color:#d4af37;"></i>Logging In &amp; Initial Account Setup
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <ol class="lh-lg mb-0" style="color:rgba(255,255,255,.88);font-size:1.02rem;">
                            <li>Click the <strong>"Login"</strong> button on the top navigation bar or the <strong>"Open Portal"</strong> button on the homepage hero.</li>
                            <li>Enter your assigned <strong>Government Email Address</strong> (e.g. <code>filetrack@mosrac.gov.zw</code>) and credentials.</li>
                            <li>If your account has the mandatory password change flag set, you will be prompted automatically to set your new password before accessing department files.</li>
                        </ol>
                    </div>

                    {{-- 02: FOLDER ARRANGEMENT --}}
                    <div class="dark-page-card p-4 p-md-5" id="step-folders">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">02</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-folder-plus me-2" style="color:#d4af37;"></i>Folder Arrangement &amp; Sidebar Navigation
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <ul class="lh-lg mb-0" style="color:rgba(255,255,255,.88);font-size:1.02rem;">
                            <li><strong>Sidebar Folder Button:</strong> Click the <strong>Folders</strong> link in the left sidebar to view all existing folders or register a new one.</li>
                            <li><strong>Adding a Folder:</strong> Click <strong>"+ Add New Folder"</strong>. Enter a unique <strong>Folder Number</strong> (e.g. <code>FOLD-2026-001</code>) and <strong>Folder Name</strong> (e.g. <code>Finance &amp; Budget Directives 2026</code>).</li>
                            <li><strong>Strict 1:1 Mapping:</strong> Every official document belongs to exactly 1 folder and cannot be placed in more than 1 folder.</li>
                        </ul>
                    </div>

                    {{-- 03: REGISTERING FILES --}}
                    <div class="dark-page-card p-4 p-md-5" id="step-file-creation">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">03</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-file-circle-plus me-2" style="color:#d4af37;"></i>Registering Files &amp; Folder Auto-Fill
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <ol class="lh-lg mb-0" style="color:rgba(255,255,255,.88);font-size:1.02rem;">
                            <li>Click the <strong>"+ New File"</strong> button from the header or dashboard.</li>
                            <li>Enter the <strong>Government File Number</strong> (e.g. <code>HR/FIN/2026/089</code>).</li>
                            <li>Select the <strong>Folder Number</strong> from the dropdown list. The <strong>Folder Name automatically populates!</strong></li>
                            <li>If the target folder does not exist yet, click the <strong>"+ New"</strong> button next to the dropdown to create it on the spot.</li>
                            <li>Fill in the File Title, Department, Subject Details/Remarks, attach an optional physical document, and click <strong>"Save &amp; Register File"</strong>.</li>
                        </ol>
                    </div>

                    {{-- 04: RECORDS DEPARTMENT FOLDER VIEW --}}
                    <div class="dark-page-card p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">04</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-folder-tree me-2" style="color:#d4af37;"></i>Records Department Folder View
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <ul class="lh-lg mb-0" style="color:rgba(255,255,255,.88);font-size:1.02rem;">
                            <li>Users at the <strong>Records Department</strong> see <strong>Folder Cards</strong> (Folder Name &amp; Folder Number) when accessing "All Files".</li>
                            <li>Clicking a folder opens the list of files housed inside that folder.</li>
                            <li>Inside the file table, Records staff can view the <strong>Admin who assigned the file</strong> and the <strong>Officer/User assigned to the job</strong>.</li>
                        </ul>
                    </div>

                    {{-- 05: TRANSFERRING & ASSIGNING FILES --}}
                    <div class="dark-page-card p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">05</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-building-columns me-2" style="color:#d4af37;"></i>Transferring Files &amp; Department Holder Display
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <p style="color:rgba(255,255,255,.88);line-height:1.8;font-size:1.02rem;" class="mb-0">
                            Whenever a document is sent to another department or officer, the <strong>Current Holder</strong> column automatically reflects the <strong>Department Name</strong> along with the specific officer handling the record (e.g. <code>Human Resources (John Doe)</code>).
                        </p>
                    </div>

                    {{-- 06: RETURN TIMEFRAMES & URGENT PRIORITY --}}
                    <div class="dark-page-card p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">06</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-clock-rotate-left me-2" style="color:#d4af37;"></i>Return Timeframes &amp; Urgent File Priority
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <ul class="lh-lg mb-0" style="color:rgba(255,255,255,.88);font-size:1.02rem;">
                            <li><strong>Return Deadlines:</strong> Permanent Secretary and Records Department staff can assign return timeframes (e.g. 30 Minutes, 1 Hour, 24 Hours).</li>
                            <li><strong>Urgent Priority:</strong> Flagged urgent files feature a bright red badge and are automatically sorted to the <strong>very top</strong> of action lists.</li>
                        </ul>
                    </div>

                    {{-- 07: TRACKING & NOTIFICATIONS --}}
                    <div class="dark-page-card p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="dark-page-badge">07</span>
                            <h3 class="fw-800 text-white mb-0">
                                <i class="fa-solid fa-bell me-2" style="color:#d4af37;"></i>Tracking File Movement &amp; Toast Notifications
                            </h3>
                        </div>
                        <hr style="border-color:rgba(255,255,255,.12);">
                        <ul class="lh-lg mb-0" style="color:rgba(255,255,255,.88);font-size:1.02rem;">
                            <li><strong>My Files:</strong> View files you created, currently hold, or participated in through dispatches in the "My Files" tab.</li>
                            <li><strong>Bottom-Right Toast Alerts:</strong> Live assignment alerts and system notifications floating in the <strong>bottom-right corner</strong> keep users updated in real time.</li>
                        </ul>
                    </div>

                </div>

                {{-- ACTION FOOTER CALL --}}
                <div class="text-center pt-5">
                    <a href="{{ route('welcome') }}" class="btn btn-outline-light btn-lg me-2 mb-2">
                        <i class="fa-solid fa-house me-2"></i>Back to Overview
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg mb-2">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Open FileTrack Portal
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
