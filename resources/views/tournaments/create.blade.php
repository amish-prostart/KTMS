@extends('layouts.app')

@section('title', 'New tournament')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('tournaments.index') }}">Tournaments</a></li>
            <li class="breadcrumb-item active" aria-current="page">New</li>
        </ol>
    </nav>

    <div class="page-header">
        <div>
            <h1>Create a tournament</h1>
            <p class="subtitle">Set it up once, then register the teams.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('tournaments.store') }}">
                @csrf
                @include('tournaments._form')

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Create tournament
                    </button>
                    <a href="{{ route('tournaments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
