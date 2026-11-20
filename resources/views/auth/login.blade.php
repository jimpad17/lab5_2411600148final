<x-guest-layout>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label small fw-bold text-muted">Username or Email</label>
            <input type="text" class="form-control" id="email" name="email"
                   value="{{ old('email') }}" placeholder="Enter username or email"
                   required autofocus autocomplete="username">
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="form-label small fw-bold text-muted">Password</label>
            <input type="password" class="form-control" id="password" name="password"
                   placeholder="Enter password" required autocomplete="current-password">
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label small text-muted" for="remember">{{ __('Remember me') }}</label>
        </div>

        <button type="submit" class="btn btn-login-orange text-white w-100 py-2 fw-bold">Login</button>

        <div class="d-flex justify-content-between mt-3 small">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-decoration-none text-warning-dark fw-semibold">
                    Forgot password?
                </a>
            @endif
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="text-decoration-none text-muted fw-semibold">
                    Create account
                </a>
            @endif
        </div>

        <div class="text-center mt-3">
            <small class="text-muted fw-bold">EXAMPLE: <span class="text-warning-dark">ADMIN / ADMIN123</span></small>
        </div>
    </form>
</x-guest-layout>
