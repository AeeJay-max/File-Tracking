<x-guest-layout>
    <h2>Verify Email</h2>
    <p class="auth-sub">Please verify your email address before continuing.</p>

    <div class="alert alert-info small mb-3">
        <i class="fa-solid fa-envelope me-2"></i>
        A verification link has been sent to your email. Click the link to verify your account.
    </div>

    @if (session('status') === 'verification-link-sent')
    <div class="alert alert-success alert-auth">
        <i class="fa-solid fa-check me-2"></i>A new verification link has been sent.
    </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="mb-2">
        @csrf
        <button type="submit" class="btn btn-primary btn-auth">
            <i class="fa-solid fa-rotate me-2"></i>Resend Verification Email
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-link w-100 text-muted small">Log Out</button>
    </form>
</x-guest-layout>
