<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FileTrack Office Portal &mdash; @yield('title', 'Login')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <style>
        :root {
            --primary: #005a2b;
            --primary-dark: #00421f;
            --bg: #e8efe9;
            --surface: #ffffff;
            --border: #d1dbd3;
            --text: #111812;
            --muted: #526255;
        }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #f7f9f7 0%, var(--bg) 100%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 54px 20px 24px;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: min(500px, 80vw);
            height: min(500px, 80vw);
            background: url('{{ asset("images/logo.png") }}') no-repeat center center / contain;
            opacity: 0.20;
            pointer-events: none;
            z-index: 0;
        }
        .auth-wrapper { width: 100%; max-width: 460px; position: relative; z-index: 1; }
        .auth-brand { text-align: center; margin-bottom: 24px; }
        .auth-brand-icon {
            width: 54px; height: 54px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 14px;
            display: inline-flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1.4rem; margin-bottom: 12px;
            box-shadow: 0 10px 24px rgba(0, 90, 43, .2);
        }
        .auth-brand-name { font-size: 1.3rem; font-weight: 800; color: var(--text); display: block; }
        .auth-brand-sub { font-size: .82rem; color: var(--muted); }
        .auth-card {
            background: var(--surface);
            border-radius: 18px;
            padding: 30px 28px;
            box-shadow: 0 12px 36px rgba(17, 26, 19, .08);
            border: 1px solid var(--border);
        }
        .auth-card h2 { font-size: 1.15rem; font-weight: 700; color: var(--text); margin-bottom: 4px; }
        .auth-card .auth-sub { font-size: .82rem; color: var(--muted); margin-bottom: 24px; }
        .form-label { font-size: .845rem; font-weight: 600; color: var(--text); margin-bottom: 5px; }
        .form-control { border-radius: 8px; border-color: var(--border); font-size: .875rem; padding: 9px 12px; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(0, 90, 43, .14); }
        .btn-auth { background: var(--primary); color: #fff; border: none; width: 100%; padding: 10px; border-radius: 8px; font-weight: 700; font-size: .9rem; margin-top: 4px; transition: background .15s; }
        .btn-auth:hover { background: var(--primary-dark); color: #fff; }
        .auth-footer { text-align: center; margin-top: 20px; font-size: .82rem; color: var(--muted); }
        .auth-footer a { color: var(--primary); font-weight: 600; text-decoration: none; }
        .auth-footer a:hover { text-decoration: underline; }
        .alert-auth { border-radius: 10px; font-size: .845rem; margin-bottom: 16px; }
        .input-group-text { background: #f8fafc; border-color: var(--border); }
        .gov-banner {
            background: linear-gradient(90deg, #111a13 0%, #005a2b 100%);
            color: #ebf3ec;
            text-align: center;
            padding: 8px 16px;
            font-size: .75rem;
            letter-spacing: .03em;
            position: fixed; top: 0; left: 0; right: 0; z-index: 9999;
        }
        .gov-banner strong { color: #fff; }
        @media (max-width: 576px) {
            body { padding: 44px 12px 16px; }
            .auth-card { padding: 24px 18px; }
        }
    </style>
</head>
<body>
    <div class="gov-banner">
        <strong>OFFICIAL GOVERNMENT PORTAL</strong> &mdash; This is an official file tracking and management system. Unauthorized access is prohibited.
    </div>

    <div class="auth-wrapper">
        <div class="auth-brand">
            <div class="auth-brand-icon" style="background:transparent;box-shadow:none;width:105px;height:105px;margin-bottom:12px;"><img src="{{ asset('images/logo.png') }}" alt="Ministry Logo" style="width:100%;height:100%;object-fit:contain;"></div>
            <span class="auth-brand-name">FileTrack Office</span>
            <span class="auth-brand-sub">Government File Tracking System</span>
        </div>
        <div class="auth-card">
            {{ $slot }}
        </div>
        <div class="auth-footer">
            <a href="{{ route('welcome') }}"><i class="fa-solid fa-arrow-left me-1"></i>Back to Portal</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // ── Eye Toggle Handler ──────────────────────────────────────
        document.addEventListener('click', function (e) {
            var toggleBtn = e.target.closest('.toggle-password-btn');
            if (!toggleBtn) return;

            var inputGroup = toggleBtn.closest('.input-group');
            if (!inputGroup) return;

            var input = inputGroup.querySelector('input[type="password"], input[type="text"]');
            if (!input) return;

            var icon = toggleBtn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
            } else {
                input.type = 'password';
                if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
            }
        });

        // ── Password Confirmation Match Handler ─────────────────────
        document.querySelectorAll('form').forEach(function (form) {
            var pwd = form.querySelector('input[name="password"]');
            var pwdConfirm = form.querySelector('input[name="password_confirmation"]');

            if (!pwd || !pwdConfirm) return;

            var targetContainer = pwdConfirm.closest('.input-group') || pwdConfirm.parentNode;
            var errDiv = document.createElement('div');
            errDiv.className = 'invalid-feedback password-mismatch-msg fw-600 mt-1';
            errDiv.style.display = 'none';
            errDiv.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i>Passwords do not match!';
            targetContainer.parentNode.insertBefore(errDiv, targetContainer.nextSibling);

            function validateMatch() {
                if (!pwdConfirm.value && !pwd.value) {
                    pwdConfirm.classList.remove('is-invalid');
                    errDiv.style.display = 'none';
                    return true;
                }

                if (pwd.value !== pwdConfirm.value) {
                    pwdConfirm.classList.add('is-invalid');
                    errDiv.style.display = 'block';
                    return false;
                } else {
                    pwdConfirm.classList.remove('is-invalid');
                    errDiv.style.display = 'none';
                    return true;
                }
            }

            pwd.addEventListener('input', function() {
                if (pwdConfirm.value) validateMatch();
            });

            pwdConfirm.addEventListener('input', validateMatch);

            form.addEventListener('submit', function (e) {
                if (pwd.value || pwdConfirm.value) {
                    if (!validateMatch()) {
                        e.preventDefault();
                        e.stopPropagation();
                        pwdConfirm.focus();
                    }
                }
            });
        });
    });
    </script>
</body>
</html>
