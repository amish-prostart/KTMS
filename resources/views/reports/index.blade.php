@extends('layouts.app')

@section('title', $tournament ? $tournament->name.' — statistics' : 'Player reports')

@section('content')
    <div class="page-header">
        <div>
            <h1>{{ $tournament ? $tournament->name.' statistics' : 'Player reports' }}</h1>
            <p class="subtitle">
                Raiding and defensive performance, generated from the recorded match data.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2 no-print">
            <a href="{{ route('reports.export', request()->query()) }}" class="btn btn-outline-secondary">
                <i class="bi bi-filetype-csv me-1"></i>Export CSV
            </a>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Raids recorded" :value="number_format($summary['raids'])"
                         :meta="$summary['raid_success_rate'].'% successful'" icon="bi-person-arms-up" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Raid points" :value="number_format($summary['raid_points'])" icon="bi-lightning-charge" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Defences recorded" :value="number_format($summary['defenses'])"
                         :meta="$summary['defense_success_rate'].'% successful'" icon="bi-shield-check" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Total points" :value="number_format($summary['total_points'])" icon="bi-star-fill" />
        </div>
    </div>

    <form method="GET" action="{{ $tournament ? route('reports.tournament', $tournament) : route('reports.index') }}"
          class="card card-body mb-4 no-print">
        <div class="row g-2 align-items-end">
            @unless ($tournament)
                <div class="col-md-3">
                    <label for="tournament" class="form-label">Tournament</label>
                    <select class="form-select" id="tournament" name="tournament">
                        <option value="">All tournaments</option>
                        @foreach ($tournaments as $option)
                            <option value="{{ $option->id }}" @selected(request('tournament') == $option->id)>
                                {{ $option->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="col-md-3">
                    <label for="team" class="form-label">Team</label>
                    <select class="form-select" id="team" name="team">
                        <option value="">All teams</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected(request('team') == $team->id)>
                                {{ $team->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endunless

            <div class="col-md-2">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role">
                    <option value="">All roles</option>
                    @foreach (\App\Models\Player::ROLES as $value => $label)
                        <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="sort" class="form-label">Sort by</label>
                <select class="form-select" id="sort" name="sort">
                    @foreach (\App\Http\Controllers\ReportController::SORTABLE as $value => $label)
                        <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="search" class="form-label">Player</label>
                <input type="search" class="form-control" id="search" name="search"
                       value="{{ request('search') }}" placeholder="Name">
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary flex-grow-1">Apply</button>
                <a href="{{ $tournament ? route('reports.tournament', $tournament) : route('reports.index') }}"
                   class="btn btn-outline-secondary">Clear</a>
            </div>

            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="played_only" name="played_only"
                           @checked(request()->boolean('played_only'))>
                    <label class="form-check-label" for="played_only">
                        Only show players who have appeared in a match
                    </label>
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Player statistics</span>
            <span class="text-muted small">{{ $statistics->total() }} {{ Str::plural('record', $statistics->total()) }}</span>
        </div>

        @if ($statistics->isEmpty())
            <div class="card-body">
                <x-empty-state icon="bi-bar-chart" title="No statistics to report"
                               message="Record some raids in a live match, then come back here." />
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-ktms table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="rank-cell">#</th>
                            <th>Player</th>
                            <th>Team</th>
                            <th class="text-end" title="Matches played">M</th>
                            <th class="text-end" title="Total raids attempted">Raids</th>
                            <th class="text-end text-success" title="Successful raids">Succ</th>
                            <th class="text-end text-danger" title="Failed raids">Fail</th>
                            <th class="text-end" title="Empty raids">Empty</th>
                            <th class="text-end" title="Raid success rate">Raid %</th>
                            <th class="text-end" title="Points from raids">Raid pts</th>
                            <th class="text-end" title="Total defences">Def</th>
                            <th class="text-end text-success" title="Successful defences">Succ</th>
                            <th class="text-end text-danger" title="Failed defences">Fail</th>
                            <th class="text-end" title="Defence success rate">Def %</th>
                            <th class="text-end" title="Points from defences">Def pts</th>
                            <th class="text-end" title="Super raids">SR</th>
                            <th class="text-end" title="Super tackles">ST</th>
                            <th class="text-end" title="Total points scored">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($statistics as $index => $stat)
                            <tr>
                                <td class="rank-cell">{{ $statistics->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="jersey-chip">{{ $stat->player->jersey_number }}</span>
                                        <span>
                                            <a href="{{ route('players.show', $stat->player) }}" class="text-decoration-none fw-semibold">
                                                {{ $stat->player->name }}
                                            </a>
                                            <small class="text-muted d-block">{{ $stat->player->role_label }}</small>
                                        </span>
                                    </div>
                                </td>
                                <td class="text-muted small">
                                    {{ $stat->player->team->name ?? '—' }}
                                    @unless ($tournament)
                                        <span class="d-block">{{ $stat->tournament->name ?? '' }}</span>
                                    @endunless
                                </td>
                                <td class="text-end">{{ $stat->matches_played }}</td>
                                <td class="text-end">{{ $stat->total_raids }}</td>
                                <td class="text-end text-success">{{ $stat->successful_raids }}</td>
                                <td class="text-end text-danger">{{ $stat->unsuccessful_raids }}</td>
                                <td class="text-end text-muted">{{ $stat->empty_raids }}</td>
                                <td class="text-end">{{ $stat->raid_success_rate }}%</td>
                                <td class="text-end fw-semibold">{{ $stat->raid_points }}</td>
                                <td class="text-end">{{ $stat->total_defenses }}</td>
                                <td class="text-end text-success">{{ $stat->successful_defenses }}</td>
                                <td class="text-end text-danger">{{ $stat->failed_defenses }}</td>
                                <td class="text-end">{{ $stat->defense_success_rate }}%</td>
                                <td class="text-end fw-semibold">{{ $stat->defense_points }}</td>
                                <td class="text-end">{{ $stat->super_raids }}</td>
                                <td class="text-end">{{ $stat->super_tackles }}</td>
                                <td class="text-end fw-bold">{{ $stat->total_points }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($statistics->hasPages())
        <div class="mt-4 no-print">
            {{ $statistics->links() }}
        </div>
    @endif

    <p class="text-muted small mt-3">
        M = matches played · SR = super raids · ST = super tackles.
        Percentages are successful attempts over total attempts.
    </p>
@endsection
