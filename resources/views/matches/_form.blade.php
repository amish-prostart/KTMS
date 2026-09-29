{{-- Shared by matches.create and matches.edit --}}
<div class="row g-3">
    <div class="col-md-6">
        <label for="home_team_id" class="form-label">Home team <span class="text-danger">*</span></label>
        <select class="form-select @error('home_team_id') is-invalid @enderror" id="home_team_id" name="home_team_id" required>
            <option value="">Select a team</option>
            @foreach ($teams as $team)
                <option value="{{ $team->id }}" @selected(old('home_team_id', $match->home_team_id) == $team->id)>
                    {{ $team->name }}
                </option>
            @endforeach
        </select>
        @error('home_team_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="away_team_id" class="form-label">Away team <span class="text-danger">*</span></label>
        <select class="form-select @error('away_team_id') is-invalid @enderror" id="away_team_id" name="away_team_id" required>
            <option value="">Select a team</option>
            @foreach ($teams as $team)
                <option value="{{ $team->id }}" @selected(old('away_team_id', $match->away_team_id) == $team->id)>
                    {{ $team->name }}
                </option>
            @endforeach
        </select>
        @error('away_team_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label for="match_number" class="form-label">Match number</label>
        <input type="number" class="form-control @error('match_number') is-invalid @enderror" id="match_number"
               name="match_number" value="{{ old('match_number', $match->match_number) }}" min="1">
        @error('match_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="round" class="form-label">Round</label>
        <input type="text" class="form-control @error('round') is-invalid @enderror" id="round" name="round"
               value="{{ old('round', $match->round) }}" placeholder="League / Semi-final">
        @error('round') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-5">
        <label for="scheduled_at" class="form-label">Scheduled for</label>
        <input type="datetime-local" class="form-control @error('scheduled_at') is-invalid @enderror" id="scheduled_at"
               name="scheduled_at" value="{{ old('scheduled_at', $match->scheduled_at?->format('Y-m-d\TH:i')) }}">
        @error('scheduled_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label for="venue" class="form-label">Venue</label>
        <input type="text" class="form-control @error('venue') is-invalid @enderror" id="venue" name="venue"
               value="{{ old('venue', $match->venue) }}">
        @error('venue') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    @if ($match->exists)
        <div class="col-md-4">
            <label for="status" class="form-label">Status</label>
            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                @foreach (\App\Models\GameMatch::STATUSES as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $match->status) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    @endif
</div>

<hr class="my-4">

<h2 class="h6 text-uppercase text-muted mb-3">Match rules</h2>

<div class="row g-3">
    <div class="col-md-4">
        <label for="half_duration_seconds" class="form-label">Half duration (seconds) <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('half_duration_seconds') is-invalid @enderror"
               id="half_duration_seconds" name="half_duration_seconds" min="60" max="3600" step="10"
               value="{{ old('half_duration_seconds', $match->half_duration_seconds ?? 1200) }}" required>
        @error('half_duration_seconds') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="raid_duration_seconds" class="form-label">Raid clock (seconds) <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('raid_duration_seconds') is-invalid @enderror"
               id="raid_duration_seconds" name="raid_duration_seconds" min="10" max="120"
               value="{{ old('raid_duration_seconds', $match->raid_duration_seconds ?? 30) }}" required>
        @error('raid_duration_seconds') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="players_per_side" class="form-label">Players per side <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('players_per_side') is-invalid @enderror"
               id="players_per_side" name="players_per_side" min="5" max="12"
               value="{{ old('players_per_side', $match->players_per_side ?? 7) }}" required>
        @if ($match->exists)
            <div class="form-text">Changing this rebuilds the match lineup.</div>
        @endif
        @error('players_per_side') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
