<x-guest-layout>
@section('title', 'Sign In')
    <h2>Sign In</h2>
    <p class="auth-sub">Enter your credentials to access the portal</p>

    @if (session('status'))
    <div class="alert alert-success alert-auth">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger alert-auth">
        @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="email">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-envelope text-muted"></i></span>
                <input id="email" type="email" name="email" class="form-control"
                    value="{{ old('email') }}" required autofocus autocomplete="username"
                    placeholder="your@email.com">
            </div>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label mb-0" for="password">Password</label>
                @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="small text-primary">Forgot password?</a>
                @endif
            </div>
            <div class="input-group mt-1">
                <span class="input-group-text"><i class="fa-solid fa-lock text-muted"></i></span>
                <input id="password" type="password" name="password" class="form-control"
                    required autocomplete="current-password" placeholder="Enter password">
            </div>
        </div>

        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                <label class="form-check-label small text-muted" for="remember_me">Remember me</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-auth">
            <i class="fa-solid fa-right-to-bracket me-2"></i>Sign In
        </button>
    </form>
</x-guest-layout>
