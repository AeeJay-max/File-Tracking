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

    {{-- NAVBAR --}}
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
                        <li class="nav-item"><a class="nav-link" href="#home">Overview</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('help') }}">Help Guide</a></li>
                        <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                        <li class="nav-item ms-lg-2"><a class="btn btn-primary btn-sm px-3" href="{{ route('login') }}"><i class="fa-solid fa-right-to-bracket me-1"></i>Login</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main>
        {{-- HERO & LIVE COMMAND CENTER --}}
        <section class="hero-section" id="home">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-7 reveal" data-reveal>
                        <span class="eyebrow-pill"><i class="fa-solid fa-shield-halved me-2"></i>Official workflow portal</span>
                        <h1>Track every file with clarity, security, and speed.</h1>
                        <p class="hero-copy">FileTrack Office keeps government file movement visible from the moment a record is created until it reaches the right department, user, or public search result.</p>
                        <div class="hero-actions">
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg"><i class="fa-solid fa-right-to-bracket me-2"></i>Open Portal</a>
                            <a href="{{ route('help') }}" class="btn btn-outline-light btn-lg"><i class="fa-solid fa-circle-question me-2"></i>Help Guide</a>
                        </div>
                    </div>
                    <div class="col-lg-5 reveal" data-reveal>
                        <div class="glass-card hero-panel">
                            <div class="panel-header mb-3">
                                <div>
                                    <span class="panel-kicker">Live command center</span>
                                    <h2>Fast, accountable, audit-ready</h2>
                                </div>
                                <span class="status-pill"><i class="fa-solid fa-circle text-success me-1"></i>Online</span>
                            </div>
                            <div class="mini-timeline">
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

        {{-- SMALL SIMPLE CONTACT SECTION --}}
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

    {{-- COMPACT FOOTER --}}
    <footer class="site-footer">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <div class="footer-brand">
                        <span class="brand-icon" style="background:transparent;box-shadow:none;"><img src="{{ asset('images/logo.png') }}" alt="Ministry Logo" style="width:64px;height:64px;object-fit:contain;"></span>
                        <div>
                            <h5>FileTrack Office</h5>
                            <p class="mb-0">Government File Tracking System</p>
                            <small class="text-muted">Ministry of Sport, Recreation, Arts and Culture</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="footer-links">
                        <a href="#home">Overview</a>
                        <a href="{{ route('public.file.search') }}">Public Search</a>
                        <a href="{{ route('help') }}">Help</a>
                        <a href="{{ route('privacy') }}">Privacy Policy</a>
                        <a href="{{ route('terms') }}">Terms of Service</a>
                    </div>
                </div>
            </div>
            <div class="footer-note mt-3">&copy; {{ date('Y') }} FileTrack Office Portal. Built for secure public-sector workflows.</div>
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