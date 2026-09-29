@extends('layouts.app')

@section('title', 'Matches')

@section('content')
    <div class="page-header">
        <div>
            <h1>Matches</h1>
            <p class="subtitle">Fixtures, live games and finished results across every tournament.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('matches.index') }}" class="card card-body mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label for="tournament" class="form-label">Tournament</label>
                <select class="form-select" id="tournament" name="tournament">
                    <option value="">All tournaments</option>
                    @foreach ($tournaments as $tournament)
                        <option value="{{ $tournament->id }}" @selected(request('tournament') == $tournament->id)>
                            {{ $tournament->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All statuses</option>
                    @foreach (\App\Models\GameMatch::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-secondary flex-grow-1">Filter</button>
                @if (request()->hasAny(['tournament', 'status']))
                    <a href="{{ route('matches.index') }}" class="btn btn-outline-secondary">Clear</a>
                @endif
            </div>
        </div>
    </form>

    @if ($matches->isEmpty())
        <div class="card">
            <div class="card-body">
                <x-empty-state icon="bi-calendar-event" title="No matches found"
                               message="Create a match from inside a tournament that has at least two teams.">
                    <a href="{{ route('tournaments.index') }}" class="btn btn-primary">Go to tournaments</a>
                </x-empty-state>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach ($matches as $match)
                <div class="col-md-6 col-xl-4">
                    <x-match-card :match="$match" :show-tournament="true" />
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $matches->links() }}
        </div>
    @endif
@endsection
