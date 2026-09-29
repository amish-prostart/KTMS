<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'name',
        'short_name',
        'logo_path',
        'primary_color',
        'secondary_color',
        'coach_name',
        'city',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class)->orderBy('jersey_number');
    }

    public function homeMatches(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'home_team_id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'away_team_id');
    }

    public function raids(): HasMany
    {
        return $this->hasMany(Raid::class, 'raiding_team_id');
    }

    public function defensiveActions(): HasMany
    {
        return $this->hasMany(DefensiveAction::class);
    }

    /**
     * Every match this team appears in, home or away.
     */
    public function matches()
    {
        return GameMatch::query()
            ->where('home_team_id', $this->id)
            ->orWhere('away_team_id', $this->id);
    }

    public function getInitialsAttribute(): string
    {
        if (filled($this->short_name)) {
            return Str::upper(Str::substr($this->short_name, 0, 3));
        }

        $words = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $initials = collect($words)
            ->filter()
            ->take(3)
            ->map(fn (string $word) => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'TM';
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (blank($this->logo_path)) {
            return null;
        }

        // Logos live on the "public" disk and are served through the storage symlink.
        if (Str::startsWith($this->logo_path, ['http://', 'https://', '/'])) {
            return $this->logo_path;
        }

        return Storage::disk('public')->url($this->logo_path);
    }
}
