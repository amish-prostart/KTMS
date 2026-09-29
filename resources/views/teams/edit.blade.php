@extends('layouts.app')

@section('title', 'Edit '.$team->name)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('tournaments.show', $tournament) }}">{{ $tournament->name }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('teams.show', $team) }}">{{ $team->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>

    <div class="page-header">
        <div>
            <h1>Edit team</h1>
            <p class="subtitle">{{ $team->name }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('teams.update', $team) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('teams._form')

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Save changes
                    </button>
                    <a href="{{ route('teams.show', $team) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-danger mt-4">
        <div class="card-header text-danger">Danger zone</div>
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <p class="mb-0 fw-semibold">Delete this team</p>
                <p class="text-muted small mb-0">
                    Removes the squad and every match record tied to it. This cannot be undone.
                </p>
            </div>
            <form method="POST" action="{{ route('teams.destroy', $team) }}"
                  onsubmit="return confirm('Delete {{ addslashes($team->name) }} and all of its players? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i>Delete team
                </button>
            </form>
        </div>
    </div>
@endsection
