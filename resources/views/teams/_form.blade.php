{{-- Shared by teams.create and teams.edit. Requires enctype="multipart/form-data" for the logo. --}}
<div class="row g-3">
    <div class="col-md-8">
        <label for="name" class="form-label">Team name <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
               value="{{ old('name', $team->name) }}" required maxlength="255">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="short_name" class="form-label">Short code</label>
        <input type="text" class="form-control @error('short_name') is-invalid @enderror" id="short_name"
               name="short_name" value="{{ old('short_name', $team->short_name) }}" maxlength="10"
               placeholder="e.g. MUM">
        <div class="form-text">Shown on the scoreboard when space is tight.</div>
        @error('short_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="coach_name" class="form-label">Coach</label>
        <input type="text" class="form-control @error('coach_name') is-invalid @enderror" id="coach_name"
               name="coach_name" value="{{ old('coach_name', $team->coach_name) }}">
        @error('coach_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="city" class="form-label">City</label>
        <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city"
               value="{{ old('city', $team->city) }}">
        @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label for="primary_color" class="form-label">Primary colour</label>
        <input type="color" class="form-control form-control-color w-100 @error('primary_color') is-invalid @enderror"
               id="primary_color" name="primary_color"
               value="{{ old('primary_color', $team->primary_color ?: '#1b4dff') }}">
        @error('primary_color') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label for="secondary_color" class="form-label">Secondary colour</label>
        <input type="color" class="form-control form-control-color w-100 @error('secondary_color') is-invalid @enderror"
               id="secondary_color" name="secondary_color"
               value="{{ old('secondary_color', $team->secondary_color ?: '#ffffff') }}">
        @error('secondary_color') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="logo" class="form-label">Team logo</label>
        <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo"
               accept="image/png,image/jpeg,image/webp,image/svg+xml">
        <div class="form-text">PNG, JPG, WEBP or SVG up to 2&nbsp;MB. Without one, the team's initials are used.</div>
        @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror

        @if ($team->exists && $team->logo_url)
            <div class="d-flex align-items-center gap-3 mt-3">
                <x-team-logo :team="$team" :size="56" />
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="remove_logo" name="remove_logo">
                    <label class="form-check-label" for="remove_logo">Remove current logo</label>
                </div>
            </div>
        @endif
    </div>
</div>
