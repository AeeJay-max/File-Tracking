<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Privacy Policy — Government File Tracking System">
    <title>Privacy Policy &mdash; FileTrack Office Portal</title>

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

    <main class="container py-5" style="max-width:920px;">
        <div class="text-center mb-5">
            <span class="eyebrow-pill mb-3" style="display:inline-block;">
                <i class="fa-solid fa-shield-halved me-2"></i>Official Information Governance
            </span>
            <h1 class="fw-800 display-5 text-white mb-2">Privacy &amp; Data Governance Policy</h1>
            <p class="text-slate-300 fs-5" style="color:#94a3b8;">How official government records and user security credentials are protected.</p>
        </div>

        <div class="glass-card p-4 p-md-5 mb-4" style="background:rgba(30,41,59,.75);border:1px solid rgba(255,255,255,.1);border-radius:16px;backdrop-filter:blur(12px);">
            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                <i class="fa-solid fa-user-shield fa-2x text-primary"></i>
                <div>
                    <h4 class="mb-0 fw-700 text-white">Data Collection &amp; Purpose</h4>
                    <span class="text-muted fs-sm">Last updated: {{ date('F Y') }}</span>
                </div>
            </div>

            <h5 class="fw-700 text-light mt-4"><i class="fa-solid fa-database text-primary me-2"></i>1. Official Information We Collect</h5>
            <p style="color:#cbd5e1;line-height:1.7;">
                The FileTrack Office Portal collects official user account details (full name, government email address, department affiliation, assigned designation) and official document metadata (folder numbers, folder names, file titles, movement histories, and dispatch timestamps).
            </p>

            <h5 class="fw-700 text-light mt-4"><i class="fa-solid fa-diagram-project text-primary me-2"></i>2. How Information Is Used</h5>
            <p style="color:#cbd5e1;line-height:1.7;">
                Information stored in the portal is used exclusively for:
            </p>
            <ul style="color:#cbd5e1;line-height:1.8;">
                <li>Managing official file dispatches between government departments and personnel.</li>
                <li>Tracking action timeframes and enforcing Permanent Secretary return deadlines.</li>
                <li>Maintaining non-repudiable audit trail logs for transparency and compliance.</li>
                <li>Dispatching toast and bell notifications regarding file assignments.</li>
            </ul>

            <h5 class="fw-700 text-light mt-4"><i class="fa-solid fa-lock text-primary me-2"></i>3. Security &amp; Role-Based Access Control</h5>
            <p style="color:#cbd5e1;line-height:1.7;">
                Data is secured using role-based access control (RBAC). Departmental staff can only access files created in or dispatched to their specific department. The Records Department maintains folder-level oversight, while Permanent Secretary reviews are strictly logged.
            </p>

            <h5 class="fw-700 text-light mt-4"><i class="fa-solid fa-key text-primary me-2"></i>4. Mandatory Credential Safety</h5>
            <p style="color:#cbd5e1;line-height:1.7;">
                All default user accounts require an immediate password change upon first login. Session tokens are automatically validated, and unauthorized access attempts trigger instant session expiration.
            </p>

            <h5 class="fw-700 text-light mt-4"><i class="fa-solid fa-headset text-primary me-2"></i>5. Administrative Contact</h5>
            <p style="color:#cbd5e1;line-height:1.7;" class="mb-0">
                For questions concerning system security or data access policies, contact your Departmental Administrator or System Administrator.
            </p>
        </div>

        <div class="text-center pt-3">
            <a href="{{ route('help') }}" class="btn btn-outline-light me-2">
                <i class="fa-solid fa-circle-question me-1"></i> View Help Guide
            </a>
            <a href="{{ route('welcome') }}" class="btn btn-primary">
                <i class="fa-solid fa-arrow-left me-1"></i> Return to Homepage
            </a>
        </div>
    </main>

    <footer class="py-4 text-center text-muted border-top border-secondary border-opacity-25 mt-5" style="font-size:.85rem;">
        &copy; {{ date('Y') }} FileTrack Office Portal &mdash; Official Government File Tracking System
    </footer>
</body>
</html>
