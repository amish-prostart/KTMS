<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name', 'KTMS') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/ktms.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-ktms shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
            <span class="brand-mark">KB</span>
            <span>KTMS</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('dashboard')) active @endif"
                       href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('tournaments.*') || request()->routeIs('teams.*') || request()->routeIs('players.*')) active @endif"
                       href="{{ route('tournaments.index') }}">
                        <i class="bi bi-trophy me-1"></i>Tournaments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('matches.*') || request()->routeIs('scoring.*') || request()->routeIs('timer.*')) active @endif"
                       href="{{ route('matches.index') }}">
                        <i class="bi bi-calendar-event me-1"></i>Matches
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('reports.*')) active @endif"
                       href="{{ route('reports.index') }}">
                        <i class="bi bi-bar-chart-line me-1"></i>Reports
                    </a>
                </li>
            </ul>

            <a href="{{ route('tournaments.create') }}" class="btn btn-sm btn-warning fw-semibold">
                <i class="bi bi-plus-lg me-1"></i>New tournament
            </a>
        </div>
    </div>
</nav>

<main class="container py-4">
    @include('partials.flash')
    @yield('content')
</main>

<footer class="container pb-4">
    <hr>
    <p class="text-muted small mb-0">
        {{ config('app.name', 'KTMS') }} — kabaddi tournament management.
        Built with Laravel {{ Illuminate\Foundation\Application::VERSION }}.
    </p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
