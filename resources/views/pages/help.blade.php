<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Help & User Guide — Government File Tracking System">
    <title>Help &amp; User Guide &mdash; FileTrack Office Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/app-custom.css') }}">
</head>

<body class="landing-page" style="background:#0f172a;color:#f8fafc;min-height:100vh;">

    {{-- NAVBAR --}}
    <header class="site-header sticky-top">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container py-1">
                <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('welcome') }}">
                    <span class="brand-icon" style="background:transparent;box-shadow:none;">
                        <img src="{{ asset('images/logo.png') }}" alt="Ministry Logo" style="width:54px;height:54px;object-fit:contain;">
                    </span>
                    <span>
                        <span class="brand-title">FileTrack Office</span>
                        <span class="brand-subtitle">Government File Tracking System</span>
                    </span>
                </a>
                <div class="ms-auto d-flex align-items-center gap-2">
                    <a href="{{ route('welcome') }}" class="btn btn-outline-light btn-sm px-3">
                        <i class="fa-solid fa-house me-1"></i> Home
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm px-3">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Login
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <main class="container py-5" style="max-width:1040px;">
        <div class="text-center mb-5">
            <span class="eyebrow-pill mb-3" style="display:inline-block;">
                <i class="fa-solid fa-graduation-cap me-2"></i>Official User Documentation &amp; Step-by-Step Guide
            </span>
            <h1 class="fw-800 display-5 text-white mb-2">FileTrack System User Guide</h1>
            <p class="text-slate-300 fs-5" style="color:#94a3b8;">Everything you need to know from logging in to creating folders, sending files, and managing deadlines.</p>
        </div>

        {{-- QUICK NAVIGATION INDEX --}}
        <div class="row g-3 mb-5">
            <div class="col-md-4">
                <a href="#step-login" class="card text-decoration-none h-100 p-3" style="background:rgba(30,41,59,.75);border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-primary p-2 fs-5" style="border-radius:10px;"><i class="fa-solid fa-right-to-bracket"></i></span>
                        <div>
                            <h6 class="fw-700 mb-0">1. Logging In</h6>
                            <small style="color:#94a3b8;">Portal access &amp; initial setup</small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="#step-folders" class="card text-decoration-none h-100 p-3" style="background:rgba(30,41,59,.75);border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-success p-2 fs-5" style="border-radius:10px;"><i class="fa-solid fa-folder-plus"></i></span>
                        <div>
                            <h6 class="fw-700 mb-0">2. Folders Setup</h6>
                            <small style="color:#94a3b8;">Folder numbers &amp; auto-fill</small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="#step-file-creation" class="card text-decoration-none h-100 p-3" style="background:rgba(30,41,59,.75);border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-warning text-dark p-2 fs-5" style="border-radius:10px;"><i class="fa-solid fa-file-circle-plus"></i></span>
                        <div>
                            <h6 class="fw-700 mb-0">3. Registering Files</h6>
                            <small style="color:#94a3b8;">Auto-populating folder names</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- DETAILED STEPS --}}
        <div class="d-flex flex-column gap-4">

            {{-- STEP 1: LOGGING IN --}}
            <div class="glass-card p-4 p-md-5" id="step-login" style="background:rgba(30,41,59,.75);border:1px solid rgba(255,255,255,.1);border-radius:16px;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="badge bg-primary px-3 py-2 fs-6" style="border-radius:8px;">STEP 1</span>
                    <h3 class="fw-700 text-white mb-0"><i class="fa-solid fa-right-to-bracket text-primary me-2"></i>How to Log In &amp; Access the Portal</h3>
                </div>
                <hr style="border-color:rgba(255,255,255,.1);">
                <ol class="lh-lg" style="color:#cbd5e1;">
                    <li>Navigate to the homepage and click the <strong>"Login"</strong> button on the top navigation bar or the <strong>"Open Portal"</strong> button in the main header.</li>
                    <li>Enter your assigned <strong>Government Email Address</strong> and password.</li>
                    <li>If this is your first time logging in or your account requires a password update, you will automatically be directed to change your password before accessing system files.</li>
                </ol>
            </div>

            {{-- STEP 2: FOLDERS SETUP --}}
            <div class="glass-card p-4 p-md-5" id="step-folders" style="background:rgba(30,41,59,.75);border:1px solid rgba(255,255,255,.1);border-radius:16px;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="badge bg-success px-3 py-2 fs-6" style="border-radius:8px;">STEP 2</span>
                    <h3 class="fw-700 text-white mb-0"><i class="fa-solid fa-folder text-success me-2"></i>Folder Arrangement &amp; Sidebar Folder Button</h3>
                </div>
                <hr style="border-color:rgba(255,255,255,.1);">
                <ul class="lh-lg" style="color:#cbd5e1;">
                    <li><strong>Sidebar Folder Button:</strong> Look at the left navigation sidebar. Click the <strong><i class="fa-solid fa-folder me-1 text-success"></i> Folders</strong> link to view all existing folders or add new ones.</li>
                    <li><strong>Adding a Folder:</strong> Click the <strong>"+ Add New Folder"</strong> button. Enter a unique <strong>Folder Number</strong> (e.g. <code>FOLD-2026-001</code>) and a <strong>Folder Name</strong> (e.g. <code>Finance Directives 2026</code>).</li>
                    <li><strong>One Folder per File:</strong> Every official document belongs to exactly 1 folder and cannot exist in more than 1 folder.</li>
                </ul>
            </div>

            {{-- STEP 3: CREATING A FILE & AUTO-FILLING FOLDER NAME --}}
            <div class="glass-card p-4 p-md-5" id="step-file-creation" style="background:rgba(30,41,59,.75);border:1px solid rgba(255,255,255,.1);border-radius:16px;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="badge bg-warning text-dark px-3 py-2 fs-6" style="border-radius:8px;">STEP 3</span>
                    <h3 class="fw-700 text-white mb-0"><i class="fa-solid fa-file-circle-plus text-warning me-2"></i>Registering a File (Folder Number &amp; Auto-Fill)</h3>
                </div>
                <hr style="border-color:rgba(255,255,255,.1);">
                <ol class="lh-lg" style="color:#cbd5e1;">
                    <li>Click <strong>"+ New File"</strong> in the main header.</li>
                    <li>Fill in the <strong>Government File Number</strong> (e.g. <code>HR/FIN/2026/089</code>).</li>
                    <li>Select the <strong>Folder Number</strong> from the dropdown list. As soon as you choose a Folder Number, the <strong>Folder Name field is automatically filled</strong>!</li>
                    <li>If the folder does not exist yet, click the <strong>"+ New"</strong> button next to the dropdown to create it inline without leaving the page.</li>
                    <li>Enter the File Name, Department, Summary/Remarks, and attach a physical document (optional), then click <strong>"Save File"</strong>.</li>
                </ol>
            </div>

            {{-- STEP 4: RECORDS DEPARTMENT FOLDER VIEW --}}
            <div class="glass-card p-4 p-md-5" style="background:rgba(30,41,59,.75);border:1px solid rgba(255,255,255,.1);border-radius:16px;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="badge bg-info text-dark px-3 py-2 fs-6" style="border-radius:8px;">STEP 4</span>
                    <h3 class="fw-700 text-white mb-0"><i class="fa-solid fa-folder-tree text-info me-2"></i>Records Department Folder View</h3>
                </div>
                <hr style="border-color:rgba(255,255,255,.1);">
                <ul class="lh-lg" style="color:#cbd5e1;">
                    <li>Users at the <strong>Records Department</strong> see <strong>Folder Cards</strong> (Folder Name &amp; Folder Number) when navigating to "All Files".</li>
                    <li>Clicking a folder opens the files contained inside that folder.</li>
                    <li>Inside the folder file list, Records staff can see the <strong>Admin who assigned the file</strong> and the <strong>Officer/User assigned to the job</strong>.</li>
                </ul>
            </div>

            {{-- STEP 5: FILE MOVEMENTS & CURRENT HOLDER --}}
            <div class="glass-card p-4 p-md-5" style="background:rgba(30,41,59,.75);border:1px solid rgba(255,255,255,.1);border-radius:16px;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="badge bg-primary px-3 py-2 fs-6" style="border-radius:8px;">STEP 5</span>
                    <h3 class="fw-700 text-white mb-0"><i class="fa-solid fa-building-columns text-primary me-2"></i>Department Name Display on Current Holder</h3>
                </div>
                <hr style="border-color:rgba(255,255,255,.1);">
                <p style="color:#cbd5e1;line-height:1.7;">
                    Whenever a document is sent to a department or assigned to an officer, the <strong>Current Holder</strong> column clearly indicates the <strong>Department Name</strong> along with the specific officer handling the record.
                </p>
            </div>

            {{-- STEP 6: PERMSEC & RECORDS RETURN TIMEFRAME & URGENT PRIORITY --}}
            <div class="glass-card p-4 p-md-5" style="background:rgba(30,41,59,.75);border:1px solid rgba(255,255,255,.1);border-radius:16px;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="badge bg-danger px-3 py-2 fs-6" style="border-radius:8px;">STEP 6</span>
                    <h3 class="fw-700 text-white mb-0"><i class="fa-solid fa-clock-rotate-left text-danger me-2"></i>Return Timeframe &amp; Urgent Priority (PermSec &amp; Records Only)</h3>
                </div>
                <hr style="border-color:rgba(255,255,255,.1);">
                <ul class="lh-lg" style="color:#cbd5e1;">
                    <li><strong>Assigning Return Timeframes:</strong> Permanent Secretary and Records Department staff can assign a timeframe for when a file must return to them (e.g. <code>30 Minutes</code>, <code>1 Hour</code>, <code>2 Hours</code>, <code>24 Hours</code>).</li>
                    <li><strong>Urgent File Color &amp; Top List Priority:</strong> Urgent files get a <strong>bold red background/badge</strong> and are placed at the <strong>very top of the list</strong> of files requiring action.</li>
                </ul>
            </div>

            {{-- STEP 7: MY FILES & TOAST NOTIFICATIONS --}}
            <div class="glass-card p-4 p-md-5" style="background:rgba(30,41,59,.75);border:1px solid rgba(255,255,255,.1);border-radius:16px;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="badge bg-secondary px-3 py-2 fs-6" style="border-radius:8px;">STEP 7</span>
                    <h3 class="fw-700 text-white mb-0"><i class="fa-solid fa-bell text-warning me-2"></i>My Files Section &amp; Bottom-Right Toast Notifications</h3>
                </div>
                <hr style="border-color:rgba(255,255,255,.1);">
                <ul class="lh-lg" style="color:#cbd5e1;">
                    <li><strong>My Files:</strong> In the "My Files" section, regular users see all files that they created, currently hold, or participated in through transfers.</li>
                    <li><strong>Bottom-Right Toast Notifications:</strong> System success and error alerts, as well as real-time file assignment updates, appear as toast notifications floating in the <strong>bottom-right corner</strong> of your screen.</li>
                </ul>
            </div>

        </div>

        <div class="text-center pt-5">
            <a href="{{ route('welcome') }}" class="btn btn-outline-light me-2">
                <i class="fa-solid fa-house me-1"></i> Back to Homepage
            </a>
            <a href="{{ route('login') }}" class="btn btn-primary">
                <i class="fa-solid fa-right-to-bracket me-1"></i> Open FileTrack Portal
            </a>
        </div>
    </main>

    <footer class="py-4 text-center text-muted border-top border-secondary border-opacity-25 mt-5" style="font-size:.85rem;">
        &copy; {{ date('Y') }} FileTrack Office Portal &mdash; Official Government File Tracking System
    </footer>
</body>
</html>
