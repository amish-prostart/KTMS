@extends('layouts.app')

@section('title', $player->name.' — player report')

@section('content')
    <nav aria-label="breadcrumb" class="no-print">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('tournaments.show', $player->team->tournament) }}">{{ $player->team->tournament->name }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('teams.show', $player->team) }}">{{ $player->team->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $player->name }}</li>
        </ol>
    </nav>

    <div class="page-header">
        <div class="d-flex gap-3 align-items-center">
            <span class="jersey-chip" style="width: 60px; height: 60px; font-size: 1.5rem; border-radius: 14px;">
                {{ $player->jersey_number }}
            </span>
            <div>
                <h1>
                    {{ $player->name }}
                    @if ($player->is_captain)
                        <span class="badge bg-warning text-dark align-middle">Captain</span>
                    @endif
                    @unless ($player->is_active)
                        <span class="badge bg-secondary align-middle">Inactive</span>
                    @endunless
                </h1>
                <p class="subtitle">
                    {{ $player->role_label }}
                    @if ($player->position_label) · {{ $player->position_label }} @endif
                    · {{ $player->team->name }}
                    @if ($player->age) · {{ $player->age }} yrs @endif
                    @if ($player->nationality) · {{ $player->nationality }} @endif
                </p>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2 no-print">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print report
            </button>
            <a href="{{ route('players.edit', $player) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
        </div>
    </div>

    {{-- Headline numbers --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Total points" :value="$statistic->total_points" icon="bi-star-fill"
                         :meta="$statistic->points_per_match.' per match'" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Matches played" :value="$statistic->matches_played" icon="bi-calendar-check" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Raid points" :value="$statistic->raid_points" icon="bi-person-arms-up"
                         :meta="$statistic->raid_points_per_raid.' per raid'" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-tile label="Defence points" :value="$statistic->defense_points" icon="bi-shield-fill-check" />
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Raiding --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-person-arms-up me-1"></i>Raiding</div>
                <div class="card-body">
                    <table class="table table-sm table-ktms mb-3">
                        <tbody>
                            <tr>
                                <th>Total raids attempted</th>
                                <td class="text-end fw-bold">{{ $statistic->total_raids }}</td>
                            </tr>
                            <tr>
                                <th class="text-success">Successful raids</th>
                                <td class="text-end fw-bold text-success">{{ $statistic->successful_raids }}</td>
                            </tr>
                            <tr>
                                <th class="text-danger">Failed raids</th>
                                <td class="text-end fw-bold text-danger">{{ $statistic->unsuccessful_raids }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Empty raids</th>
                                <td class="text-end">{{ $statistic->empty_raids }}</td>
                            </tr>
                            <tr>
                                <th>Touch points</th>
                                <td class="text-end">{{ $statistic->touch_points }}</td>
                            </tr>
                            <tr>
                                <th>Bonus points</th>
                                <td class="text-end">{{ $statistic->bonus_points }}</td>
                            </tr>
                            <tr>
                                <th>Super raids</th>
                                <td class="text-end">{{ $statistic->super_raids }}</td>
                            </tr>
                            <tr>
                                <th>Do-or-die raids</th>
                                <td class="text-end">
                                    {{ $statistic->do_or_die_conversions }} / {{ $statistic->do_or_die_raids }}
                                    <small class="text-muted">({{ $statistic->do_or_die_conversion_rate }}%)</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Raid success rate</span>
                        <span class="fw-bold">{{ $statistic->raid_success_rate }}%</span>
                    </div>
                    <div class="mini-bar">
                        <span style="width: {{ $statistic->raid_success_rate }}%"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Defending --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-shield-check me-1"></i>Defending</div>
                <div class="card-body">
                    <table class="table table-sm table-ktms mb-3">
                        <tbody>
                            <tr>
                                <th>Total defences</th>
                                <td class="text-end fw-bold">{{ $statistic->total_defenses }}</td>
                            </tr>
                            <tr>
                                <th class="text-success">Successful defences</th>
                                <td class="text-end fw-bold text-success">{{ $statistic->successful_defenses }}</td>
                            </tr>
                            <tr>
                                <th class="text-danger">Failed defences</th>
                                <td class="text-end fw-bold text-danger">{{ $statistic->failed_defenses }}</td>
                            </tr>
                            <tr>
                                <th>Super tackles</th>
                                <td class="text-end">{{ $statistic->super_tackles }}</td>
                            </tr>
                            <tr>
                                <th>Defence points</th>
                                <td class="text-end">{{ $statistic->defense_points }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Defence success rate</span>
                        <span class="fw-bold">{{ $statistic->defense_success_rate }}%</span>
                    </div>
                    <div class="mini-bar">
                        <span style="width: {{ $statistic->defense_success_rate }}%"></span>
                    </div>

                    <dl class="row mt-4 mb-0 small">
                        @if ($player->height_cm)
                            <dt class="col-6 text-muted fw-normal">Height</dt>
                            <dd class="col-6 text-end">{{ $player->height_cm }} cm</dd>
                        @endif
                        @if ($player->weight_kg)
                            <dt class="col-6 text-muted fw-normal">Weight</dt>
                            <dd class="col-6 text-end">{{ $player->weight_kg }} kg</dd>
                        @endif
                        @if ($player->date_of_birth)
                            <dt class="col-6 text-muted fw-normal">Born</dt>
                            <dd class="col-6 text-end">{{ $player->date_of_birth->format('d M Y') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>

    {{-- Raw event logs, the records the totals above are built from --}}
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">Recent raids</div>
                @if ($raids->isEmpty())
                    <div class="card-body"><x-empty-state icon="bi-clipboard" title="No raids recorded" /></div>
                @else
                    <div class="table-responsive">
                        <table class="table table-ktms mb-0">
                            <thead>
                                <tr>
                                    <th>Raid</th>
                                    <th>Match</th>
                                    <th>Result</th>
                                    <th class="text-end">Pts</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($raids as $raid)
                                    <tr>
                                        <td>
                                            #{{ $raid->raid_number }}
                                            @if ($raid->is_super_raid)
                                                <span class="badge bg-warning text-dark">Super</span>
                                            @endif
                                            @if ($raid->is_do_or_die)
                                                <span class="badge bg-danger">DOD</span>
                                            @endif
                                        </td>
                                        <td class="small">
                                            <a href="{{ route('matches.show', $raid->match) }}" class="text-decoration-none">
                                                vs {{ $raid->defendingTeam->short_name ?? $raid->defendingTeam->name }}
                                            </a>
                                            <span class="text-muted d-block">H{{ $raid->half }} · {{ \App\Models\GameMatch::formatClock($raid->game_clock_at_event ?? 0) }}</span>
                                        </td>
                                        <td><span class="badge bg-{{ $raid->result_colour }}">{{ $raid->result_label }}</span></td>
                                        <td class="text-end fw-bold">{{ $raid->raid_points }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">Recent defensive actions</div>
                @if ($defenses->isEmpty())
                    <div class="card-body"><x-empty-state icon="bi-clipboard" title="No defensive actions recorded" /></div>
                @else
                    <div class="table-responsive">
                        <table class="table table-ktms mb-0">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Against</th>
                                    <th>Stopped?</th>
                                    <th class="text-end">Pts</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($defenses as $defense)
                                    <tr>
                                        <td>{{ $defense->action_type_label }}</td>
                                        <td class="small">
                                            {{ $defense->raid?->raider?->name ?? '—' }}
                                            <span class="text-muted d-block">H{{ $defense->half }} · {{ \App\Models\GameMatch::formatClock($defense->game_clock_at_event ?? 0) }}</span>
                                        </td>
                                        <td>
                                            @if ($defense->is_successful)
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <span class="badge bg-danger">No</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold">{{ $defense->points_awarded }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
