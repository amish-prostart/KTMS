@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header">
        <div>
            <h1>Control room</h1>
            <p class="subtitle">Live matches, fixtures and the players making the difference.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('matches.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-calendar-event me-1"></i>All matches
            </a>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-bar-chart-line me-1"></i>Reports
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Tournaments" :value="$counts['tournaments']" icon="bi-trophy" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Teams" :value="$counts['teams']" icon="bi-shield-shaded" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Players" :value="$counts['players']" icon="bi-people" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Matches" :value="$counts['matches']" icon="bi-calendar-event" />
        </div>
    </div>

    {{-- Live first: this is what an operator opens the dashboard for. --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                @if ($liveMatches->isNotEmpty())
                    <span class="live-dot"></span>
                @endif
                Live now
            </span>
            <span class="badge bg-secondary">{{ $liveMatches->count() }}</span>
        </div>
        <div class="card-body">
            @if ($liveMatches->isEmpty())
                <x-empty-state icon="bi-broadcast" title="No match in progress"
                               message="Start a scheduled match from its timer console to go live.">
                    <a href="{{ route('matches.index') }}" class="btn btn-sm btn-primary">View fixtures</a>
                </x-empty-state>
            @else
                <div class="row g-3">
                    @foreach ($liveMatches as $match)
                        <div class="col-12 col-xl-6">
                            <x-match-card :match="$match" :show-tournament="true" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">Next up</div>
                <div class="card-body p-0">
                    @if ($upcomingMatches->isEmpty())
                        <x-empty-state icon="bi-calendar-plus" title="No fixtures scheduled" />
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($upcomingMatches as $match)
                                <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                    <div class="min-w-0">
                                        <a href="{{ route('matches.show', $match) }}"
                                           class="fw-semibold text-decoration-none d-block text-truncate">
                                            {{ $match->title }}
                                        </a>
                                        <small class="text-muted">
                                            {{ $match->tournament->name }}
                                            @if ($match->scheduled_at)
                                                · {{ $match->scheduled_at->format('d M, H:i') }}
                                            @endif
                                        </small>
                                    </div>
                                    <a href="{{ route('timer.console', $match) }}" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                        Start
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">Recent results</div>
                <div class="card-body p-0">
                    @if ($recentResults->isEmpty())
                        <x-empty-state icon="bi-clipboard-data" title="No completed matches yet" />
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($recentResults as $match)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <a href="{{ route('matches.show', $match) }}"
                                           class="fw-semibold text-decoration-none text-truncate">
                                            {{ $match->title }}
                                        </a>
                                        <span class="badge bg-dark flex-shrink-0">
                                            {{ $match->home_score }} – {{ $match->away_score }}
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        {{ $match->winnerTeam?->name ? $match->winnerTeam->name.' won' : 'Tied' }}
                                        · {{ $match->tournament->name }}
                                    </small>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-person-arms-up me-1"></i>Top raiders</div>
                <div class="card-body p-0">
                    @if ($topRaiders->isEmpty())
                        <x-empty-state icon="bi-graph-up" title="No raid data recorded yet" />
                    @else
                        <div class="table-responsive">
                            <table class="table table-ktms mb-0">
                                <thead>
                                    <tr>
                                        <th class="rank-cell">#</th>
                                        <th>Player</th>
                                        <th class="text-end">Raids</th>
                                        <th class="text-end">Success</th>
                                        <th class="text-end">Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topRaiders as $index => $stat)
                                        <tr>
                                            <td class="rank-cell">{{ $index + 1 }}</td>
                                            <td>
                                                <a href="{{ route('players.show', $stat->player) }}" class="text-decoration-none">
                                                    {{ $stat->player->name }}
                                                </a>
                                                <small class="text-muted d-block">{{ $stat->player->team->name }}</small>
                                            </td>
                                            <td class="text-end">{{ $stat->total_raids }}</td>
                                            <td class="text-end">{{ $stat->raid_success_rate }}%</td>
                                            <td class="text-end fw-bold">{{ $stat->raid_points }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-shield-check me-1"></i>Top defenders</div>
                <div class="card-body p-0">
                    @if ($topDefenders->isEmpty())
                        <x-empty-state icon="bi-graph-up" title="No defensive data recorded yet" />
                    @else
                        <div class="table-responsive">
                            <table class="table table-ktms mb-0">
                                <thead>
                                    <tr>
                                        <th class="rank-cell">#</th>
                                        <th>Player</th>
                                        <th class="text-end">Tackles</th>
                                        <th class="text-end">Success</th>
                                        <th class="text-end">Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topDefenders as $index => $stat)
                                        <tr>
                                            <td class="rank-cell">{{ $index + 1 }}</td>
                                            <td>
                                                <a href="{{ route('players.show', $stat->player) }}" class="text-decoration-none">
                                                    {{ $stat->player->name }}
                                                </a>
                                                <small class="text-muted d-block">{{ $stat->player->team->name }}</small>
                                            </td>
                                            <td class="text-end">{{ $stat->total_defenses }}</td>
                                            <td class="text-end">{{ $stat->defense_success_rate }}%</td>
                                            <td class="text-end fw-bold">{{ $stat->defense_points }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($tournaments->isNotEmpty())
        <h2 class="h5 mt-4 mb-3">Tournaments</h2>
        <div class="row g-3">
            @foreach ($tournaments as $tournament)
                <div class="col-sm-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <span class="badge bg-{{ $tournament->status_colour }} mb-2">{{ $tournament->status_label }}</span>
                            <h3 class="h6 mb-1">
                                <a href="{{ route('tournaments.show', $tournament) }}" class="text-decoration-none">
                                    {{ $tournament->name }}
                                </a>
                            </h3>
                            <p class="text-muted small mb-0">
                                {{ $tournament->teams_count }} teams · {{ $tournament->matches_count }} matches
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
