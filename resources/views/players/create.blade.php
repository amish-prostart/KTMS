@extends('layouts.app')

@section('title', 'Add player')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('tournaments.show', $team->tournament) }}">{{ $team->tournament->name }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('teams.show', $team) }}">{{ $team->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add player</li>
        </ol>
    </nav>

    <div class="page-header">
        <div>
            <h1>Add a player</h1>
            <p class="subtitle">Joining {{ $team->name }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('players.store', $team) }}">
                @csrf
                @include('players._form')

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Add player
                    </button>
                    <a href="{{ route('teams.show', $team) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
