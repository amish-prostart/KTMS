@extends('layouts.app')

@section('title', $match->title)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('tournaments.show', $match->tournament) }}">{{ $match->tournament->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $match->title }}</li>
        </ol>
    </nav>

    <div class="page-header">
        <div>
            <span class="badge bg-{{ $match->status_colour }} mb-2">
                @if ($match->isLive())<span class="live-dot"></span>@endif
                {{ $match->status_label }}
            </span>
            <h1>{{ $match->title }}</h1>
            <p class="subtitle">
                @if ($match->match_number) Match {{ $match->match_number }} · @endif
                @if ($match->round) {{ $match->round }} · @endif
                Half {{ $match->current_half }}
                @if ($match->venue) · {{ $match->venue }} @endif
                @if ($match->scheduled_at) · {{ $match->scheduled_at->format('d M Y, H:i') }} @endif
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('matches.live', $match) }}" class="btn btn-dark" target="_blank">
                <i class="bi bi-display me-1"></i>Live display
            </a>
            <a href="{{ route('scoring.console', $match) }}" class="btn btn-warning">
                <i class="bi bi-joystick me-1"></i>Scoring console
            </a>
            <a href="{{ route('timer.console', $match) }}" class="btn btn-info">
                <i class="bi bi-stopwatch me-1"></i>Timer console
            </a>
            <a href="{{ route('matches.edit', $match) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
        </div>
    </div>

    {{-- Scoreline --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center text-center g-3">
                <div class="col-5">
                    <div class="d-flex flex-column align-items-center gap-2">
                        <x-team-logo :team="$match->homeTeam" :size="64" />
                        <div>
                            <a href="{{ route('teams.show', $match->homeTeam) }}" class="h5 d-block text-decoration-none mb-0">
                                {{ $match->homeTeam->name }}
                            </a>
                            <small class="text-muted">
                                {{ $match->home_players_on_court }}/{{ $match->playersPerSide() }} on the mat
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="display-5 fw-bold">
                        {{ $match->home_score }} <span class="text-muted fs-4">–</span> {{ $match->away_score }}
                    </div>
                    <small class="text-muted d-block">
                        {{ \App\Models\GameMatch::formatClock($match->effectiveGameClockRemaining()) }} left
                    </small>
                    @if ($match->winnerTeam)
                        <span class="badge bg-success mt-2">{{ $match->winnerTeam->name }} won</span>
                    @elseif ($match->isCompleted())
                        <span class="badge bg-secondary mt-2">Tied</span>
                    @endif
                </div>
                <div class="col-5">
                    <div class="d-flex flex-column align-items-center gap-2">
                        <x-team-logo :team="$match->awayTeam" :size="64" />
                        <div>
                            <a href="{{ route('teams.show', $match->awayTeam) }}" class="h5 d-block text-decoration-none mb-0">
                                {{ $match->awayTeam->name }}
                            </a>
                            <small class="text-muted">
                                {{ $match->away_players_on_court }}/{{ $match->playersPerSide() }} on the mat
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Raids recorded" :value="$raids->count()" icon="bi-person-arms-up" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Successful raids"
                         :value="$raids->where('result', \App\Models\Raid::RESULT_SUCCESSFUL)->count()"
                         icon="bi-check2-circle" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Super raids" :value="$raids->where('is_super_raid', true)->count()"
                         icon="bi-stars" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Tackles"
                         :value="$match->defensiveActions()->where('is_successful', true)->count()"
                         icon="bi-shield-check" />
        </div>
    </div>

    <div class="row g-4">
        {{-- Raid log --}}
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header">Raid log</div>
                @if ($raids->isEmpty())
                    <div class="card-body">
                        <x-empty-state icon="bi-clipboard-data" title="No raids recorded yet"
                                       message="Open the scoring console to start recording this match.">
                            <a href="{{ route('scoring.console', $match) }}" class="btn btn-sm btn-warning">
                                Open scoring console
                            </a>
                        </x-empty-state>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-ktms mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Raider</th>
                                    <th>Result</th>
                                    <th>Detail</th>
                                    <th>Defence</th>
                                    <th class="text-end">Raid</th>
                                    <th class="text-end">Def</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($raids as $raid)
                                    <tr>
                                        <td>
                                            {{ $raid->raid_number }}
                                            <small class="text-muted d-block">
                                                H{{ $raid->half }} · {{ \App\Models\GameMatch::formatClock($raid->game_clock_at_event ?? 0) }}
                                            </small>
                                        </td>
                                        <td>
                                            <a href="{{ route('players.show', $raid->raider) }}" class="text-decoration-none">
                                                {{ $raid->raider->display_name }}
                                            </a>
                                            <small class="text-muted d-block">{{ $raid->raidingTeam->short_name ?? $raid->raidingTeam->name }}</small>
                                        </td>
                                        <td><span class="badge bg-{{ $raid->result_colour }}">{{ $raid->result_label }}</span></td>
                                        <td class="small">
                                            @if ($raid->touch_points) {{ $raid->touch_points }} touch @endif
                                            @if ($raid->bonus_points) <span class="badge bg-info text-dark">Bonus</span> @endif
                                            @if ($raid->is_super_raid) <span class="badge bg-warning text-dark">Super raid</span> @endif
                                            @if ($raid->is_do_or_die) <span class="badge bg-danger">Do-or-die</span> @endif
                                        </td>
                                        <td class="small text-muted">
                                            @forelse ($raid->defensiveActions as $action)
                                                <div>
                                                    {{ $action->defender->name }}
                                                    @if ($action->is_successful)
                                                        <i class="bi bi-check-circle-fill text-success"></i>
                                                    @else
                                                        <i class="bi bi-x-circle text-danger"></i>
                                                    @endif
                                                </div>
                                            @empty
                                                —
                                            @endforelse
                                        </td>
                                        <td class="text-end fw-bold">{{ $raid->raid_points }}</td>
                                        <td class="text-end fw-bold">{{ $raid->defending_points }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Timeline --}}
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">Match timeline</div>
                <div class="card-body">
                    @if ($timeline->isEmpty())
                        <x-empty-state icon="bi-list-ul" title="Nothing recorded yet" />
                    @else
                        <div class="timeline">
                            @foreach ($timeline as $event)
                                <div class="timeline-item @if ($event->is_undone) opacity-50 text-decoration-line-through @endif">
                                    <span class="timeline-clock">{{ $event->clock_label }}</span>
                                    <span>
                                        <i class="bi {{ $event->icon }} me-1"></i>{{ $event->description }}
                                        @if ($event->team_name)
                                            <small class="text-muted d-block">{{ $event->team_name }}</small>
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($topPerformers->isNotEmpty())
        <div class="card mt-4">
            <div class="card-header">Standout players in this tournament</div>
            <div class="table-responsive">
                <table class="table table-ktms mb-0">
                    <thead>
                        <tr>
                            <th class="rank-cell">#</th>
                            <th>Player</th>
                            <th>Team</th>
                            <th class="text-end">Raids</th>
                            <th class="text-end">Raid pts</th>
                            <th class="text-end">Def pts</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topPerformers as $index => $stat)
                            <tr>
                                <td class="rank-cell">{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('players.show', $stat->player) }}" class="text-decoration-none">
                                        {{ $stat->player->display_name }}
                                    </a>
                                </td>
                                <td class="text-muted">{{ $stat->player->team->name }}</td>
                                <td class="text-end">{{ $stat->total_raids }}</td>
                                <td class="text-end">{{ $stat->raid_points }}</td>
                                <td class="text-end">{{ $stat->defense_points }}</td>
                                <td class="text-end fw-bold">{{ $stat->total_points }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
