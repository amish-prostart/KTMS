@extends('layouts.app')

@section('title', 'Tournaments')

@section('content')
    <div class="page-header">
        <div>
            <h1>Tournaments</h1>
            <p class="subtitle">Every league and knockout you are running.</p>
        </div>
        <a href="{{ route('tournaments.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>New tournament
        </a>
    </div>

    <form method="GET" action="{{ route('tournaments.index') }}" class="card card-body mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label for="search" class="form-label">Search</label>
                <input type="search" class="form-control" id="search" name="search"
                       value="{{ request('search') }}" placeholder="Name or city">
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All statuses</option>
                    @foreach (\App\Models\Tournament::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-secondary flex-grow-1">Filter</button>
                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('tournaments.index') }}" class="btn btn-outline-secondary">Clear</a>
                @endif
            </div>
        </div>
    </form>

    @if ($tournaments->isEmpty())
        <div class="card">
            <div class="card-body">
                <x-empty-state icon="bi-trophy" title="No tournaments yet"
                               message="Create a tournament, add the squads, then start scoring matches.">
                    <a href="{{ route('tournaments.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Create the first tournament
                    </a>
                </x-empty-state>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach ($tournaments as $tournament)
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-{{ $tournament->status_colour }}">{{ $tournament->status_label }}</span>
                                @if ($tournament->season)
                                    <span class="text-muted small">{{ $tournament->season }}</span>
                                @endif
                            </div>

                            <h2 class="h5 mb-1">
                                <a href="{{ route('tournaments.show', $tournament) }}" class="text-decoration-none">
                                    {{ $tournament->name }}
                                </a>
                            </h2>

                            <p class="text-muted small mb-3">
                                @if ($tournament->city || $tournament->venue)
                                    <i class="bi bi-geo-alt me-1"></i>{{ collect([$tournament->venue, $tournament->city])->filter()->implode(', ') }}<br>
                                @endif
                                @if ($tournament->start_date)
                                    <i class="bi bi-calendar3 me-1"></i>{{ $tournament->start_date->format('d M Y') }}
                                    @if ($tournament->end_date) – {{ $tournament->end_date->format('d M Y') }} @endif
                                @endif
                            </p>

                            <div class="d-flex gap-3 small">
                                <span><strong>{{ $tournament->teams_count }}</strong> teams</span>
                                <span><strong>{{ $tournament->matches_count }}</strong> matches</span>
                            </div>
                        </div>
                        <div class="card-footer bg-white d-flex gap-1">
                            <a href="{{ route('tournaments.show', $tournament) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i> Open
                            </a>
                            <a href="{{ route('tournaments.edit', $tournament) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a href="{{ route('reports.tournament', $tournament) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-bar-chart"></i> Stats
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $tournaments->links() }}
        </div>
    @endif
@endsection
