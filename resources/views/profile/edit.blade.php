@extends('layouts.app')

@section('title', 'Profile')

@section('heading')
    Account <span class="text-warning fw-bold">Profile</span>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white pt-3 border-bottom-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-person-circle text-primary me-2"></i> Profile Information</h5>
                </div>
                <div class="card-body p-4">
                    @if (session('status') === 'profile-updated')
                        <div class="alert alert-success py-2 small fw-bold">Profile updated successfully.</div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label for="name" class="form-label small fw-bold text-muted">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', Auth::user()->name) }}" required>
                            @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label small fw-bold text-muted">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', Auth::user()->email) }}" required>
                            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary px-4 fw-bold">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white pt-3 border-bottom-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock text-warning me-2"></i> Update Password</h5>
                </div>
                <div class="card-body p-4">
                    @if (session('status') === 'password-updated')
                        <div class="alert alert-success py-2 small fw-bold">Password updated successfully.</div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label small fw-bold text-muted">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required autocomplete="current-password">
                            @error('current_password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label small fw-bold text-muted">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password">
                            @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label small fw-bold text-muted">Confirm New Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                        </div>

                        <button type="submit" class="btn btn-warning px-4 fw-bold">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-start border-danger border-4">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="fw-bold text-danger mb-1">Delete Account</h5>
                <p class="small text-muted mb-0">Permanently remove your account and its data.</p>
            </div>
            <form method="POST" action="{{ route('profile.destroy') }}"
                  onsubmit="return confirm('Are you sure you want to delete your account?');">
                @csrf
                @method('DELETE')
                <input type="password" name="password" class="form-control mb-2" placeholder="Confirm password" required>
                @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
                <button type="submit" class="btn btn-outline-danger px-4">Delete Account</button>
            </form>
        </div>
    </div>
@endsection
