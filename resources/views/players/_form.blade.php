{{-- Shared by players.create and players.edit --}}
<div class="row g-3">
    <div class="col-md-8">
        <label for="name" class="form-label">Player name <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
               value="{{ old('name', $player->name) }}" required maxlength="255">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="jersey_number" class="form-label">Jersey number <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('jersey_number') is-invalid @enderror" id="jersey_number"
               name="jersey_number" value="{{ old('jersey_number', $player->jersey_number) }}"
               min="0" max="999" required>
        <div class="form-text">Must be unique within the squad.</div>
        @error('jersey_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
            @foreach (\App\Models\Player::ROLES as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $player->role) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="position" class="form-label">Court position</label>
        <select class="form-select @error('position') is-invalid @enderror" id="position" name="position">
            <option value="">Not specified</option>
            @foreach (\App\Models\Player::POSITIONS as $value => $label)
                <option value="{{ $value }}" @selected(old('position', $player->position) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="date_of_birth" class="form-label">Date of birth</label>
        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth"
               name="date_of_birth" value="{{ old('date_of_birth', $player->date_of_birth?->format('Y-m-d')) }}">
        @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="height_cm" class="form-label">Height (cm)</label>
        <input type="number" class="form-control @error('height_cm') is-invalid @enderror" id="height_cm"
               name="height_cm" value="{{ old('height_cm', $player->height_cm) }}" min="100" max="250">
        @error('height_cm') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="weight_kg" class="form-label">Weight (kg)</label>
        <input type="number" class="form-control @error('weight_kg') is-invalid @enderror" id="weight_kg"
               name="weight_kg" value="{{ old('weight_kg', $player->weight_kg) }}" min="30" max="200">
        @error('weight_kg') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="nationality" class="form-label">Nationality</label>
        <input type="text" class="form-control @error('nationality') is-invalid @enderror" id="nationality"
               name="nationality" value="{{ old('nationality', $player->nationality) }}" maxlength="100">
        @error('nationality') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 d-flex align-items-end">
        <div class="w-100">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="is_captain" name="is_captain"
                       @checked(old('is_captain', $player->is_captain))>
                <label class="form-check-label" for="is_captain">
                    Team captain
                    <span class="text-muted small d-block">Setting this clears the captain flag on any other player.</span>
                </label>
            </div>
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active"
                       @checked(old('is_active', $player->exists ? $player->is_active : true))>
                <label class="form-check-label" for="is_active">
                    Available for selection
                    <span class="text-muted small d-block">Inactive players are left out of new match lineups.</span>
                </label>
            </div>
        </div>
    </div>
</div>
