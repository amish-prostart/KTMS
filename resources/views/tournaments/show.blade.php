@extends('layouts.app')

@section('title', $tournament->name)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('tournaments.index') }}">Tournaments</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $tournament->name }}</li>
        </ol>
    </nav>

    <div class="page-header">
        <div>
            <span class="badge bg-{{ $tournament->status_colour }} mb-2">{{ $tournament->status_label }}</span>
            <h1>{{ $tournament->name }}</h1>
            <p class="subtitle">
                @if ($tournament->season) Season {{ $tournament->season }} · @endif
                {{ collect([$tournament->venue, $tournament->city])->filter()->implode(', ') ?: 'Venue to be confirmed' }}
                @if ($tournament->start_date)
                    · {{ $tournament->start_date->format('d M Y') }}
                    @if ($tournament->end_date) – {{ $tournament->end_date->format('d M Y') }} @endif
                @endif
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('teams.create', $tournament) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Add team
            </a>
            <a href="{{ route('matches.create', $tournament) }}"
               class="btn btn-warning @if ($tournament->teams->count() < 2) disabled @endif">
                <i class="bi bi-calendar-plus me-1"></i>Create match
            </a>
            <a href="{{ route('reports.tournament', $tournament) }}" class="btn btn-outline-secondary">
                <i class="bi bi-bar-chart-line me-1"></i>Statistics
            </a>
            <a href="{{ route('tournaments.edit', $tournament) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
        </div>
    </div>

    @if ($tournament->description)
        <div class="alert alert-light border">{{ $tournament->description }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Teams" :value="$tournament->teams->count()" icon="bi-shield-shaded" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Players" :value="$tournament->teams->sum('players_count')" icon="bi-people" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Matches" :value="$matches->count()" icon="bi-calendar-event" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Completed"
                         :value="$matches->where('status', \App\Models\GameMatch::STATUS_COMPLETED)->count()"
                         icon="bi-check2-circle" />
        </div>
    </div>

    {{-- Teams --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Teams</span>
            <a href="{{ route('teams.create', $tournament) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus-lg"></i> Add
            </a>
        </div>
        <div class="card-body">
            @if ($tournament->teams->isEmpty())
                <x-empty-state icon="bi-shield-shaded" title="No teams registered"
                               message="A match needs at least two teams.">
                    <a href="{{ route('teams.create', $tournament) }}" class="btn btn-sm btn-primary">Add the first team</a>
                </x-empty-state>
            @else
                <div class="row g-3">
                    @foreach ($tournament->teams as $team)
                        <div class="col-sm-6 col-lg-4">
                            <div class="card h-100">
                                <div class="card-body d-flex gap-3">
                                    <x-team-logo :team="$team" :size="52" />
                                    <div class="min-w-0">
                                        <h3 class="h6 mb-1 text-truncate">
                                            <a href="{{ route('teams.show', $team) }}" class="text-decoration-none">
                                                {{ $team->name }}
                                            </a>
                                        </h3>
                                        <p class="text-muted small mb-0">
                                            {{ $team->players_count }} {{ Str::plural('player', $team->players_count) }}
                                            @if ($team->city) · {{ $team->city }} @endif
                                        </p>
                                        @if ($team->coach_name)
                                            <p class="text-muted small mb-0">Coach: {{ $team->coach_name }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Matches --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Matches</span>
            @if ($tournament->teams->count() >= 2)
                <a href="{{ route('matches.create', $tournament) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus-lg"></i> Create
                </a>
            @endif
        </div>
        <div class="card-body">
            @if ($matches->isEmpty())
                <x-empty-state icon="bi-calendar-event" title="No matches created"
                               message="Create a fixture between two registered teams to start scoring." />
            @else
                <div class="row g-3">
                    @foreach ($matches as $match)
                        <div class="col-md-6 col-xl-4">
                            <x-match-card :match="$match" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Leaders --}}
    @if ($leaders->isNotEmpty())
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Leading scorers</span>
                <a href="{{ route('reports.tournament', $tournament) }}" class="btn btn-sm btn-outline-secondary">
                    Full report
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-ktms mb-0">
                    <thead>
                        <tr>
                            <th class="rank-cell">#</th>
                            <th>Player</th>
                            <th>Team</th>
                            <th class="text-end">Raid pts</th>
                            <th class="text-end">Defence pts</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leaders as $index => $stat)
                            <tr>
                                <td class="rank-cell">{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('players.show', $stat->player) }}" class="text-decoration-none">
                                        {{ $stat->player->display_name }}
                                    </a>
                                </td>
                                <td class="text-muted">{{ $stat->player->team->name }}</td>
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
