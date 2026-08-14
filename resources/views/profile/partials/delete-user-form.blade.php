<p class="text-muted small mb-3">
    Once your account is deleted, all data will be permanently removed. This cannot be undone.
</p>

<button type="button" class="btn btn-outline-danger btn-sm"
    data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
    <i class="fa-solid fa-trash me-1"></i>Delete My Account
</button>

<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-danger">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Delete Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="{{ route('profile.destroy') }}" class="portal-form">
                @csrf
                @method('delete')
                <div class="modal-body">
                    <p class="text-muted small">
                        Please enter your password to confirm permanent account deletion.
                    </p>
                    <div>
                        <label class="form-label" for="del-password">
                            Password <span class="required-star">*</span>
                        </label>
                        <input id="del-password" name="password" type="password"
                            class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                            placeholder="Enter your password">
                        @error('password', 'userDeletion')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-trash me-1"></i>Delete Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
