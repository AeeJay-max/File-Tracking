<form method="post" action="{{ route('profile.password.update') }}" class="portal-form" id="updatePasswordForm">
    @csrf
    @method('put')

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="cp-current">Current Password <span class="required-star">*</span></label>
            <div class="input-group">
                <input id="cp-current" name="current_password" type="password"
                    class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                    autocomplete="current-password" placeholder="Your current password" required>
                <button type="button" class="btn btn-outline-secondary toggle-password-btn" tabindex="-1" title="Toggle Password Visibility">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
            @error('current_password', 'updatePassword')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label" for="cp-new">New Password <span class="required-star">*</span></label>
            <div class="input-group">
                <input id="cp-new" name="password" type="password"
                    class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                    autocomplete="new-password" placeholder="Min. 8 characters" required>
                <button type="button" class="btn btn-outline-secondary toggle-password-btn" tabindex="-1" title="Toggle Password Visibility">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
            @error('password', 'updatePassword')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label" for="cp-confirm">Confirm New Password <span class="required-star">*</span></label>
            <div class="input-group">
                <input id="cp-confirm" name="password_confirmation" type="password"
                    class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                    autocomplete="new-password" placeholder="Repeat new password" required>
                <button type="button" class="btn btn-outline-secondary toggle-password-btn" tabindex="-1" title="Toggle Password Visibility">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
            @error('password_confirmation', 'updatePassword')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mt-3 d-flex align-items-center gap-3">
        <button type="submit" class="btn-portal-primary" id="updatePasswordSubmitBtn">
            <i class="fa-solid fa-lock me-1"></i>Update Password
        </button>
        @if(session('status') === 'password-updated')
        <span class="text-success small"><i class="fa-solid fa-check me-1"></i>Password updated successfully.</span>
        @endif
    </div>
</form>
