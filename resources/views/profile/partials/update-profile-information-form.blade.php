<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}" class="portal-form">
    @csrf
    @method('patch')

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="pi-name">Full Name <span class="required-star">*</span></label>
            <input id="pi-name" name="name" type="text"
                class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $user->name) }}" required autofocus>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="pi-email">Email Address <span class="required-star">*</span></label>
            <input id="pi-email" name="email" type="email"
                class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email', $user->email) }}" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
            <div class="mt-2 p-2 bg-warning-subtle rounded" style="font-size:.8rem;">
                <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>
                Your email is unverified.
                <button form="send-verification" class="btn btn-link btn-sm p-0 ms-1">
                    Resend verification email
                </button>
            </div>
            @if(session('status') === 'verification-link-sent')
            <div class="mt-1 text-success small"><i class="fa-solid fa-check me-1"></i>Verification email sent.</div>
            @endif
            @endif
        </div>

        <div class="col-md-6">
            <label class="form-label" for="pi-phone">Phone Number</label>
            <input id="pi-phone" name="phone" type="text"
                class="form-control @error('phone') is-invalid @enderror"
                value="{{ old('phone', $user->phone) }}"
                placeholder="+1 234 567 8900">
            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="pi-contact">Contact Number</label>
            <input id="pi-contact" name="contact_number" type="text"
                class="form-control @error('contact_number') is-invalid @enderror"
                value="{{ old('contact_number', $user->contact_number) }}"
                placeholder="Alternative contact">
            @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="mt-3 d-flex align-items-center gap-3">
        <button type="submit" class="btn-portal-primary">
            <i class="fa-solid fa-floppy-disk me-1"></i>Save Changes
        </button>
        @if(session('status') === 'profile-updated')
        <span class="text-success small"><i class="fa-solid fa-check me-1"></i>Profile saved.</span>
        @endif
    </div>
</form>
