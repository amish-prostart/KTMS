@extends('layouts.app')

@section('title', 'Edit match')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('tournaments.show', $tournament) }}">{{ $tournament->name }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('matches.show', $match) }}">{{ $match->title }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>

    <div class="page-header">
        <div>
            <h1>Edit match</h1>
            <p class="subtitle">{{ $match->title }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('matches.update', $match) }}">
                @csrf
                @method('PUT')
                @include('matches._form')

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Save changes
                    </button>
                    <a href="{{ route('matches.show', $match) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-warning mt-4">
        <div class="card-header text-warning-emphasis">Reset match</div>
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <p class="mb-0 fw-semibold">Clear all scoring for this match</p>
                <p class="text-muted small mb-0">
                    Wipes the score, raids, defensive actions and timeline, then rebuilds the lineup.
                    Player statistics are recalculated. Useful after a test run.
                </p>
            </div>
            <form method="POST" action="{{ route('matches.reset', $match) }}"
                  onsubmit="return confirm('Reset this match? All recorded raids, defensive actions and scores will be permanently deleted.');">
                @csrf
                <button type="submit" class="btn btn-outline-warning">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset match
                </button>
            </form>
        </div>
    </div>

    <div class="card border-danger mt-4">
        <div class="card-header text-danger">Danger zone</div>
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <p class="mb-0 fw-semibold">Delete this match</p>
                <p class="text-muted small mb-0">Removes the fixture and every record attached to it.</p>
            </div>
            <form method="POST" action="{{ route('matches.destroy', $match) }}"
                  onsubmit="return confirm('Delete this match and all of its records? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i>Delete match
                </button>
            </form>
        </div>
    </div>
@endsection
