@extends('layouts.app')

@section('title', $team->name)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('tournaments.index') }}">Tournaments</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tournaments.show', $team->tournament) }}">{{ $team->tournament->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $team->name }}</li>
        </ol>
    </nav>

    <div class="page-header">
        <div class="d-flex gap-3 align-items-center">
            <x-team-logo :team="$team" :size="72" />
            <div>
                <h1>{{ $team->name }}</h1>
                <p class="subtitle">
                    @if ($team->short_name) {{ $team->short_name }} · @endif
                    {{ $team->players->count() }} {{ Str::plural('player', $team->players->count()) }}
                    @if ($team->city) · {{ $team->city }} @endif
                    @if ($team->coach_name) · Coach {{ $team->coach_name }} @endif
                </p>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('players.create', $team) }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i>Add player
            </a>
            <a href="{{ route('teams.edit', $team) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i>Edit team
            </a>
        </div>
    </div>

    @php
        $totals = [
            'raids' => $statistics->sum('total_raids'),
            'raid_points' => $statistics->sum('raid_points'),
            'defenses' => $statistics->sum('total_defenses'),
            'defense_points' => $statistics->sum('defense_points'),
        ];
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Total raids" :value="$totals['raids']" icon="bi-person-arms-up" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Raid points" :value="$totals['raid_points']" icon="bi-lightning-charge" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Total defences" :value="$totals['defenses']" icon="bi-shield-check" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Defence points" :value="$totals['defense_points']" icon="bi-shield-fill-check" />
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Squad</span>
            <a href="{{ route('players.create', $team) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-person-plus"></i> Add
            </a>
        </div>

        @if ($team->players->isEmpty())
            <div class="card-body">
                <x-empty-state icon="bi-people" title="No players in this squad"
                               message="Add players with their jersey numbers so they can be picked during a match.">
                    <a href="{{ route('players.create', $team) }}" class="btn btn-sm btn-primary">Add the first player</a>
                </x-empty-state>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-ktms mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Player</th>
                            <th>Role</th>
                            <th>Position</th>
                            <th class="text-end">Raids</th>
                            <th class="text-end">Raid pts</th>
                            <th class="text-end">Defences</th>
                            <th class="text-end">Def pts</th>
                            <th class="text-end">Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($team->players as $player)
                            @php $stat = $statistics->get($player->id); @endphp
                            <tr>
                                <td><span class="jersey-chip">{{ $player->jersey_number }}</span></td>
                                <td>
                                    <a href="{{ route('players.show', $player) }}" class="text-decoration-none fw-semibold">
                                        {{ $player->name }}
                                    </a>
                                    @if ($player->is_captain)
                                        <span class="badge bg-warning text-dark ms-1" title="Captain">C</span>
                                    @endif
                                    @unless ($player->is_active)
                                        <span class="badge bg-secondary ms-1">Inactive</span>
                                    @endunless
                                </td>
                                <td>{{ $player->role_label }}</td>
                                <td class="text-muted">{{ $player->position_label ?? '—' }}</td>
                                <td class="text-end">{{ $stat?->total_raids ?? 0 }}</td>
                                <td class="text-end">{{ $stat?->raid_points ?? 0 }}</td>
                                <td class="text-end">{{ $stat?->total_defenses ?? 0 }}</td>
                                <td class="text-end">{{ $stat?->defense_points ?? 0 }}</td>
                                <td class="text-end fw-bold">{{ $stat?->total_points ?? 0 }}</td>
                                <td class="text-end">
                                    <a href="{{ route('players.edit', $player) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header">Matches</div>
        <div class="card-body">
            @if ($matches->isEmpty())
                <x-empty-state icon="bi-calendar-event" title="No matches for this team yet" />
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
@endsection
