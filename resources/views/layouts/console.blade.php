<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Operator console') · {{ config('app.name', 'KTMS') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/ktms.css') }}" rel="stylesheet">
    <style>
        /* Dim the whole console while an action is in flight. */
        body.ktms-busy .console-actions { opacity: 0.55; pointer-events: none; }
    </style>
    @stack('styles')
</head>
<body class="console-shell">
<nav class="navbar navbar-dark navbar-ktms shadow-sm">
    <div class="container-fluid px-3 px-lg-4">
        <div class="d-flex align-items-center gap-3 text-white">
            <a href="{{ route('matches.show', $match) }}" class="btn btn-sm btn-outline-light">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <div class="fw-bold">@yield('console-name', 'Console')</div>
                <div class="small opacity-75">
                    {{ $match->homeTeam->name }} vs {{ $match->awayTeam->name }} ·
                    {{ $match->tournament->name }}
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-{{ $match->status_colour }}" data-match-status>{{ $match->status_label }}</span>
            <span class="text-white small">
                <i class="bi bi-circle-fill" data-connection title="Connection"></i>
                <span class="d-none d-md-inline ms-1">live sync</span>
            </span>
            <a href="{{ route('matches.live', $match) }}" target="_blank" class="btn btn-sm btn-outline-light">
                <i class="bi bi-display me-1"></i><span class="d-none d-md-inline">Display</span>
            </a>
            @yield('console-switch')
        </div>
    </div>
</nav>

<main class="container-fluid px-3 px-lg-4 py-4">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/ktms-live.js') }}"></script>
@stack('scripts')
</body>
</html>
