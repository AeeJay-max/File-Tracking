<x-guest-layout>
    <h2>Confirm Password</h2>
    <p class="auth-sub">This is a secure area. Please confirm your password before continuing.</p>

    @if ($errors->any())
    <div class="alert alert-danger alert-auth">
        @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input id="password" type="password" name="password" class="form-control"
                required autocomplete="current-password" placeholder="Enter your password">
        </div>
        <button type="submit" class="btn btn-primary btn-auth">
            <i class="fa-solid fa-shield-halved me-2"></i>Confirm
        </button>
    </form>
</x-guest-layout>
