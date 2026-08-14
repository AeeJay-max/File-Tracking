<form method="post" action="{{ route('profile.password.update') }}" class="portal-form">
    @csrf
    @method('put')

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="cp-current">Current Password <span class="required-star">*</span></label>
            <input id="cp-current" name="current_password" type="password"
                class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                autocomplete="current-password" placeholder="Your current password">
            @error('current_password', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label" for="cp-new">New Password <span class="required-star">*</span></label>
            <input id="cp-new" name="password" type="password"
                class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                autocomplete="new-password" placeholder="Min. 8 characters">
            @error('password', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label" for="cp-confirm">Confirm New Password <span class="required-star">*</span></label>
            <input id="cp-confirm" name="password_confirmation" type="password"
                class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                autocomplete="new-password" placeholder="Repeat new password">
            @error('password_confirmation', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mt-3 d-flex align-items-center gap-3">
        <button type="submit" class="btn-portal-primary">
            <i class="fa-solid fa-lock me-1"></i>Update Password
        </button>
        @if(session('status') === 'password-updated')
        <span class="text-success small"><i class="fa-solid fa-check me-1"></i>Password updated successfully.</span>
        @endif
    </div>
</form>
