<x-guest-layout>
    <h2>Reset Password</h2>
    <p class="auth-sub">Enter a new password for your account.</p>

    @if ($errors->any())
    <div class="alert alert-danger alert-auth">
        @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label class="form-label" for="email">Email Address</label>
            <input id="email" type="email" name="email" class="form-control"
                value="{{ old('email', $request->email) }}" required autocomplete="username">
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">New Password</label>
            <input id="password" type="password" name="password" class="form-control"
                required autocomplete="new-password" placeholder="Minimum 8 characters">
        </div>

        <div class="mb-3">
            <label class="form-label" for="password_confirmation">Confirm New Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control"
                required autocomplete="new-password" placeholder="Repeat new password">
        </div>

        <button type="submit" class="btn btn-primary btn-auth">
            <i class="fa-solid fa-lock me-2"></i>Reset Password
        </button>
    </form>
</x-guest-layout>
