@extends('layouts.app')

@section('title', 'Create match')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('tournaments.show', $tournament) }}">{{ $tournament->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create match</li>
        </ol>
    </nav>

    <div class="page-header">
        <div>
            <h1>Create a match</h1>
            <p class="subtitle">
                In {{ $tournament->name }}. The starting lineup is filled automatically from each squad.
            </p>
        </div>
    </div>

    @if ($teams->count() < 2)
        <div class="card">
            <div class="card-body">
                <x-empty-state icon="bi-shield-exclamation" title="Not enough teams"
                               message="A match needs two registered teams in this tournament.">
                    <a href="{{ route('teams.create', $tournament) }}" class="btn btn-primary">Add a team</a>
                </x-empty-state>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('matches.store', $tournament) }}">
                    @csrf
                    @include('matches._form')

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Create match
                        </button>
                        <a href="{{ route('tournaments.show', $tournament) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
