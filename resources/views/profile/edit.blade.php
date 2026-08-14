@extends('layouts.app')
@section('title', 'My Profile')

@section('breadcrumb')
<li class="breadcrumb-item active">Profile</li>
@endsection

@section('content')
@php
    $verifiedEmail = $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && $user->hasVerifiedEmail();
@endphp

{{-- ── PROFILE HEADER CARD ──────────────────────────────────────────── --}}
<div class="portal-card mb-4">
    <div class="card-body py-4">
        <div class="d-flex align-items-center gap-4 flex-wrap">

            {{-- Avatar --}}
            <div class="profile-avatar-wrap position-relative">
                @if($user->photo_url)
                <img src="{{ $user->photo_url }}"
                     alt="{{ $user->name }}"
                     class="profile-avatar-img">
                @else
                <div class="profile-avatar-initials">{{ $user->initials }}</div>
                @endif

                {{-- Edit photo overlay --}}
                <button type="button" class="profile-avatar-edit-btn"
                    data-bs-toggle="modal" data-bs-target="#photoModal"
                    title="Change photo">
                    <i class="fa-solid fa-camera"></i>
                </button>
            </div>

            {{-- Name + meta --}}
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <h2 class="mb-0 fw-800" style="font-size:1.5rem;">{{ $user->name }}</h2>
                    <span class="badge-status badge-role-{{ $user->role }}">
                        {{ ucfirst(str_replace('_',' ', $user->role)) }}
                    </span>
                    @if($verifiedEmail)
                    <span class="badge-status badge-active" title="Email verified">
                        <i class="fa-solid fa-circle-check me-1"></i>Verified
                    </span>
                    @else
                    <span class="badge-status badge-pending" title="Email not verified">
                        <i class="fa-solid fa-circle-exclamation me-1"></i>Unverified
                    </span>
                    @endif
                </div>
                <div class="text-muted fs-sm mb-2">{{ $user->email }}</div>
                <div class="d-flex gap-3 flex-wrap" style="font-size:.82rem;">
                    <span class="text-muted">
                        <i class="fa-solid fa-building-columns me-1"></i>
                        {{ $user->department->name ?? 'No Department' }}
                    </span>
                    <span class="text-muted">
                        <i class="fa-solid fa-id-badge me-1"></i>
                        {{ $user->designation->name ?? 'No Designation' }}
                    </span>
                    @if($user->employee_code)
                    <span class="text-muted">
                        <i class="fa-solid fa-barcode me-1"></i>
                        {{ $user->employee_code }}
                    </span>
                    @endif
                    <span class="{{ $user->is_active ? 'text-success' : 'text-danger' }}">
                        <i class="fa-solid fa-circle me-1" style="font-size:.55rem;vertical-align:middle;"></i>
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="row g-3">

    {{-- ── LEFT COLUMN ──────────────────────────────────────────────── --}}
    <div class="col-lg-8">

        {{-- Profile Information --}}
        <div class="portal-card mb-3">
            <div class="card-header">
                <i class="fa-solid fa-user-pen me-2 text-primary"></i>Profile Information
            </div>
            <div class="card-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        {{-- Change Password --}}
        <div class="portal-card mb-3">
            <div class="card-header">
                <i class="fa-solid fa-lock me-2 text-primary"></i>Change Password
            </div>
            <div class="card-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        {{-- Danger Zone --}}
        <div class="portal-card border-danger-subtle">
            <div class="card-header text-danger">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>Delete Account
            </div>
            <div class="card-body">
                @include('profile.partials.delete-user-form')
            </div>
        </div>

    </div>

    {{-- ── RIGHT COLUMN ─────────────────────────────────────────────── --}}
    <div class="col-lg-4">

        {{-- Account Summary --}}
        <div class="portal-card mb-3">
            <div class="card-header">
                <i class="fa-solid fa-circle-info me-2 text-primary"></i>Account Summary
            </div>
            <div class="card-body">
                <dl class="profile-dl">
                    <dt>Role</dt>
                    <dd><span class="badge-status badge-role-{{ $user->role }}">{{ ucfirst(str_replace('_',' ',$user->role)) }}</span></dd>

                    <dt>Email Status</dt>
                    <dd>
                        @if($verifiedEmail)
                        <span class="badge-status badge-active"><i class="fa-solid fa-check me-1"></i>Verified</span>
                        @else
                        <span class="badge-status badge-pending"><i class="fa-solid fa-clock me-1"></i>Unverified</span>
                        @endif
                    </dd>

                    <dt>Department</dt>
                    <dd>{{ $user->department->name ?? '—' }}</dd>

                    <dt>Designation</dt>
                    <dd>{{ $user->designation->name ?? '—' }}</dd>

                    @if($user->employee_code)
                    <dt>Employee Code</dt>
                    <dd><code>{{ $user->employee_code }}</code></dd>
                    @endif

                    @if($user->phone)
                    <dt>Phone</dt>
                    <dd>{{ $user->phone }}</dd>
                    @endif

                    @if($user->contact_number)
                    <dt>Contact</dt>
                    <dd>{{ $user->contact_number }}</dd>
                    @endif

                    <dt>Account Status</dt>
                    <dd>
                        @if($user->is_active)
                        <span class="badge-status badge-active">Active</span>
                        @else
                        <span class="badge-status badge-rejected">Inactive</span>
                        @endif
                    </dd>

                    <dt>Member Since</dt>
                    <dd class="text-muted fs-sm">{{ $user->created_at->format('d M Y') }}</dd>
                </dl>

                {{-- Resend verification --}}
                @if(!$verifiedEmail)
                <hr class="my-3">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm w-100">
                        <i class="fa-solid fa-envelope me-1"></i>Resend Verification Email
                    </button>
                </form>
                @if(session('status') === 'verification-link-sent')
                <div class="alert alert-success mt-2 py-2 small">Verification email sent!</div>
                @endif
                @endif
            </div>
        </div>

        {{-- Photo management --}}
        @if($user->photo_url)
        <div class="portal-card">
            <div class="card-header">
                <i class="fa-solid fa-image me-2 text-primary"></i>Profile Photo
            </div>
            <div class="card-body text-center">
                <img src="{{ $user->photo_url }}" alt="{{ $user->name }}"
                     class="rounded-circle mb-3" style="width:100px;height:100px;object-fit:cover;">
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-sm btn-outline-primary"
                        data-bs-toggle="modal" data-bs-target="#photoModal">
                        <i class="fa-solid fa-camera me-1"></i>Change
                    </button>
                    <form method="POST" action="{{ route('profile.photo.delete') }}" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger"
                            onclick="return confirm('Remove profile photo?')">
                            <i class="fa-solid fa-trash me-1"></i>Remove
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

{{-- ── PHOTO UPLOAD MODAL ──────────────────────────────────────── --}}
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-camera me-2"></i>Update Profile Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('profile.photo.upload') }}" enctype="multipart/form-data" class="portal-form">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Photo <span class="required-star">*</span></label>
                        <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror"
                               accept="image/jpeg,image/png,image/gif" required>
                        <div class="form-text">Accepted: JPG, PNG, GIF. Max size: 2 MB.</div>
                        @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    {{-- Preview --}}
                    <div id="photoPreview" class="text-center d-none">
                        <img id="photoPreviewImg" src="" alt="Preview"
                             class="rounded-circle" style="width:100px;height:100px;object-fit:cover;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-portal-primary">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Upload Photo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.profile-avatar-wrap { position: relative; flex-shrink: 0; }
.profile-avatar-img {
    width: 96px; height: 96px; border-radius: 50%;
    object-fit: cover; border: 3px solid #e2e8f0;
}
.profile-avatar-initials {
    width: 96px; height: 96px; border-radius: 50%;
    background: linear-gradient(135deg, #005a2b, #00421f);
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem; font-weight: 800; border: 3px solid #e2e8f0;
    user-select: none;
}
.profile-avatar-edit-btn {
    position: absolute; bottom: 2px; right: 2px;
    width: 28px; height: 28px; border-radius: 50%;
    background: #005a2b; color: #fff; border: 2px solid #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: .7rem; cursor: pointer; transition: background .15s;
}
.profile-avatar-edit-btn:hover { background: #00421f; }
.profile-dl { display: grid; grid-template-columns: auto 1fr; gap: 8px 16px; font-size: .845rem; margin: 0; }
.profile-dl dt { font-weight: 600; color: var(--muted); white-space: nowrap; }
.profile-dl dd { margin: 0; }
.border-danger-subtle { border-color: #fca5a5 !important; }
</style>
@endpush

@push('scripts')
<script>
document.querySelector('input[name="photo"]')?.addEventListener('change', function(e) {
    var file = e.target.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(ev) {
        document.getElementById('photoPreviewImg').src = ev.target.result;
        document.getElementById('photoPreview').classList.remove('d-none');
    };
    reader.readAsDataURL(file);
});
</script>
@endpush
