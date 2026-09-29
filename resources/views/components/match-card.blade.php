@props([
    'match',
    'showTournament' => false,
])

<div class="card h-100">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <span class="badge bg-{{ $match->status_colour }}">
                    @if ($match->status === \App\Models\GameMatch::STATUS_LIVE)
                        <span class="live-dot"></span>
                    @endif
                    {{ $match->status_label }}
                </span>
                @if ($match->match_number)
                    <span class="text-muted small ms-1">Match {{ $match->match_number }}</span>
                @endif
            </div>
            @if ($match->scheduled_at)
                <span class="text-muted small">{{ $match->scheduled_at->format('d M Y, H:i') }}</span>
            @endif
        </div>

        @if ($showTournament && $match->tournament)
            <p class="text-muted small mb-2">
                <i class="bi bi-trophy me-1"></i>{{ $match->tournament->name }}
            </p>
        @endif

        <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
            <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                @if ($match->homeTeam)
                    <x-team-logo :team="$match->homeTeam" :size="34" />
                @endif
                <span class="fw-semibold text-truncate">{{ $match->homeTeam->name ?? 'TBD' }}</span>
            </div>
            <span class="fs-4 fw-bold">{{ $match->home_score }}</span>
        </div>

        <div class="d-flex align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                @if ($match->awayTeam)
                    <x-team-logo :team="$match->awayTeam" :size="34" />
                @endif
                <span class="fw-semibold text-truncate">{{ $match->awayTeam->name ?? 'TBD' }}</span>
            </div>
            <span class="fs-4 fw-bold">{{ $match->away_score }}</span>
        </div>

        @if ($match->isCompleted() && $match->winnerTeam)
            <p class="small text-success mt-2 mb-0">
                <i class="bi bi-trophy-fill me-1"></i>{{ $match->winnerTeam->name }} won
            </p>
        @elseif ($match->isCompleted())
            <p class="small text-muted mt-2 mb-0"><i class="bi bi-dash-circle me-1"></i>Match tied</p>
        @endif
    </div>

    <div class="card-footer bg-white border-top-0 pt-0">
        <div class="d-flex flex-wrap gap-1">
            <a href="{{ route('matches.show', $match) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-eye"></i> Details
            </a>
            <a href="{{ route('matches.live', $match) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                <i class="bi bi-display"></i> Display
            </a>
            <a href="{{ route('scoring.console', $match) }}" class="btn btn-sm btn-outline-warning">
                <i class="bi bi-joystick"></i> Score
            </a>
            <a href="{{ route('timer.console', $match) }}" class="btn btn-sm btn-outline-info">
                <i class="bi bi-stopwatch"></i> Timer
            </a>
        </div>
    </div>
</div>
