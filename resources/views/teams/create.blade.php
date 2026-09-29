@extends('layouts.app')

@section('title', 'Add team')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('tournaments.index') }}">Tournaments</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tournaments.show', $tournament) }}">{{ $tournament->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add team</li>
        </ol>
    </nav>

    <div class="page-header">
        <div>
            <h1>Add a team</h1>
            <p class="subtitle">Registering for {{ $tournament->name }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('teams.store', $tournament) }}" enctype="multipart/form-data">
                @csrf
                @include('teams._form')

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Add team
                    </button>
                    <a href="{{ route('tournaments.show', $tournament) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
