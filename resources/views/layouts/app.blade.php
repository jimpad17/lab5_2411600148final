<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - JimTrack Fitness</title>

    <!-- Bootstrap 5 CSS (same UI toolkit used in Laboratory Exercise 4) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/jimtrack.css') }}">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm py-2">
        <div class="container-fluid px-3">
            <a class="navbar-brand fw-bold text-white" href="{{ route('dashboard') }}">
                <i class="bi bi-lightning-charge-fill text-warning me-2"></i>JimTrack Fitness
                <span class="text-warning small ms-1">Inventory</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3">
                        <span class="navbar-text text-light small">
                            Welcome, <span class="text-warning fw-bold">{{ Auth::user()->name }}</span>
                            <span class="badge role-badge ms-1">{{ strtoupper(Auth::user()->role) }}</span>
                        </span>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning btn-sm px-3">
                                <i class="bi bi-box-arrow-right me-1"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar vh-100 p-3">
                <div class="position-sticky pt-2 d-flex flex-column" style="min-height: calc(100vh - 90px);">
                    <small class="text-uppercase text-muted fw-bold px-3 mb-2" style="font-size: .68rem;">Main Menu</small>
                    <ul class="nav flex-column gap-1">
                        @php
                            $navItems = [
                                ['dashboard', 'Dashboard', 'bi-speedometer2', 'dashboard'],
                                ['inventory.index', 'Inventory', 'bi-clipboard-data', 'inventory*'],
                                ['products.index', 'Products', 'bi-box-seam', 'products*'],
                                ['brands.index', 'Brands', 'bi-award', 'brands*'],
                                ['categories.index', 'Categories', 'bi-tags', 'categories*'],
                            ];
                        @endphp
                        @foreach ($navItems as [$route, $label, $icon, $pattern])
                            <li class="nav-item">
                                <a class="nav-link rounded px-3 py-2 {{ request()->routeIs($pattern) ? 'active' : '' }}"
                                   href="{{ route($route) }}">
                                    <i class="bi {{ $icon }} me-2"></i> {{ $label }}
                                </a>
                            </li>
                        @endforeach

                        @if (Auth::user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link rounded px-3 py-2 {{ request()->routeIs('users*') ? 'active' : '' }}"
                                   href="{{ route('users.index') }}">
                                    <i class="bi bi-people me-2"></i> Users
                                </a>
                            </li>
                        @endif

                        <li class="nav-item">
                            <a class="nav-link rounded px-3 py-2 {{ request()->routeIs('activity-logs*') ? 'active' : '' }}"
                               href="{{ route('activity-logs.index') }}">
                                <i class="bi bi-clock-history me-2"></i> Activity Log
                            </a>
                        </li>
                    </ul>
                </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap align-items-center pb-2 mb-4 border-bottom">
                    <h2 class="fw-bold text-dark mb-0">
                        @hasSection('heading')
                            @yield('heading')
                        @else
                            Welcome, <span class="text-warning fw-bold">{{ Auth::user()->name }}</span>!
                        @endif
                    </h2>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show py-2 small fw-bold" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show py-2 small fw-bold" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger py-2 small" role="alert">
                        <strong>Please correct the following:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
