<x-guest-layout>
@section('title', 'Register')
    <h2>Create Account</h2>
    <p class="auth-sub">Register to access the file tracking portal</p>

    @if ($errors->any())
    <div class="alert alert-danger alert-auth">
        @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="name">Full Name</label>
            <input id="name" type="text" name="name" class="form-control"
                value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Your full name">
        </div>

        <div class="mb-3">
            <label class="form-label" for="email">Email Address</label>
            <input id="email" type="email" name="email" class="form-control"
                value="{{ old('email') }}" required autocomplete="username" placeholder="your@email.com">
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input id="password" type="password" name="password" class="form-control"
                required autocomplete="new-password" placeholder="Minimum 8 characters">
        </div>

        <div class="mb-3">
            <label class="form-label" for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control"
                required autocomplete="new-password" placeholder="Repeat password">
        </div>

        <button type="submit" class="btn btn-primary btn-auth">
            <i class="fa-solid fa-user-plus me-2"></i>Register
        </button>

        <div class="text-center mt-3 small">
            Already have an account? <a href="{{ route('login') }}">Sign in</a>
        </div>
    </form>
</x-guest-layout>
