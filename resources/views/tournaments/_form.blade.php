{{-- Shared by tournaments.create and tournaments.edit --}}
<div class="row g-3">
    <div class="col-md-8">
        <label for="name" class="form-label">Tournament name <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
               value="{{ old('name', $tournament->name) }}" required maxlength="255">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="season" class="form-label">Season</label>
        <input type="text" class="form-control @error('season') is-invalid @enderror" id="season" name="season"
               value="{{ old('season', $tournament->season) }}" placeholder="2026">
        @error('season') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                  rows="2" maxlength="2000">{{ old('description', $tournament->description) }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="venue" class="form-label">Venue</label>
        <input type="text" class="form-control @error('venue') is-invalid @enderror" id="venue" name="venue"
               value="{{ old('venue', $tournament->venue) }}">
        @error('venue') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="city" class="form-label">City</label>
        <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city"
               value="{{ old('city', $tournament->city) }}">
        @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="start_date" class="form-label">Start date</label>
        <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date"
               name="start_date" value="{{ old('start_date', $tournament->start_date?->format('Y-m-d')) }}">
        @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="end_date" class="form-label">End date</label>
        <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date"
               name="end_date" value="{{ old('end_date', $tournament->end_date?->format('Y-m-d')) }}">
        @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
            @foreach (\App\Models\Tournament::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $tournament->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<hr class="my-4">

<h2 class="h6 text-uppercase text-muted mb-3">Match defaults</h2>
<p class="text-muted small">
    New matches inherit these values. Each match can still be adjusted individually.
</p>

<div class="row g-3">
    <div class="col-md-4">
        <label for="half_duration_seconds" class="form-label">Half duration (seconds) <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('half_duration_seconds') is-invalid @enderror"
               id="half_duration_seconds" name="half_duration_seconds" min="60" max="3600" step="10"
               value="{{ old('half_duration_seconds', $tournament->half_duration_seconds ?? 1200) }}" required>
        <div class="form-text">1200 seconds is a standard 20 minute half.</div>
        @error('half_duration_seconds') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="raid_duration_seconds" class="form-label">Raid clock (seconds) <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('raid_duration_seconds') is-invalid @enderror"
               id="raid_duration_seconds" name="raid_duration_seconds" min="10" max="120"
               value="{{ old('raid_duration_seconds', $tournament->raid_duration_seconds ?? 30) }}" required>
        <div class="form-text">Standard kabaddi raids are 30 seconds.</div>
        @error('raid_duration_seconds') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="players_per_side" class="form-label">Players per side <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('players_per_side') is-invalid @enderror"
               id="players_per_side" name="players_per_side" min="5" max="12"
               value="{{ old('players_per_side', $tournament->players_per_side ?? 7) }}" required>
        @error('players_per_side') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
