@extends('layouts.app')

@section('title', 'Edit '.$player->name)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('teams.show', $team) }}">{{ $team->name }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('players.show', $player) }}">{{ $player->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>

    <div class="page-header">
        <div>
            <h1>Edit player</h1>
            <p class="subtitle">{{ $player->display_name }} · {{ $team->name }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('players.update', $player) }}">
                @csrf
                @method('PUT')
                @include('players._form')

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Save changes
                    </button>
                    <a href="{{ route('players.show', $player) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-danger mt-4">
        <div class="card-header text-danger">Danger zone</div>
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <p class="mb-0 fw-semibold">Remove this player</p>
                <p class="text-muted small mb-0">
                    Deletes the player along with their raid and defensive records. This cannot be undone.
                </p>
            </div>
            <form method="POST" action="{{ route('players.destroy', $player) }}"
                  onsubmit="return confirm('Remove {{ addslashes($player->name) }} and all of their recorded statistics?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i>Remove player
                </button>
            </form>
        </div>
    </div>
@endsection
