<x-guest-layout>
@section('title', 'Forgot Password')
    <h2>Forgot Password</h2>
    <p class="auth-sub">Enter your email address and we'll send you a reset link.</p>

    @if (session('status'))
    <div class="alert alert-success alert-auth">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger alert-auth">
        @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="email">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-envelope text-muted"></i></span>
                <input id="email" type="email" name="email" class="form-control"
                    value="{{ old('email') }}" required autofocus placeholder="your@email.com">
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-auth">
            <i class="fa-solid fa-paper-plane me-2"></i>Send Reset Link
        </button>

        <div class="text-center mt-3 small">
            <a href="{{ route('login') }}"><i class="fa-solid fa-arrow-left me-1"></i>Back to Sign In</a>
        </div>
    </form>
</x-guest-layout>
