<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Tournament extends Model
{
    use HasFactory;

    public const STATUS_UPCOMING = 'upcoming';
    public const STATUS_ONGOING = 'ongoing';
    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        self::STATUS_UPCOMING => 'Upcoming',
        self::STATUS_ONGOING => 'Ongoing',
        self::STATUS_COMPLETED => 'Completed',
    ];

    protected $fillable = [
        'name',
        'slug',
        'season',
        'description',
        'venue',
        'city',
        'start_date',
        'end_date',
        'status',
        'half_duration_seconds',
        'raid_duration_seconds',
        'players_per_side',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'half_duration_seconds' => 'integer',
            'raid_duration_seconds' => 'integer',
            'players_per_side' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Keep the slug in sync so tournaments always have a clean, unique URL key.
        static::saving(function (Tournament $tournament): void {
            if (blank($tournament->slug)) {
                $tournament->slug = static::uniqueSlug($tournament->name, $tournament->id);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'tournament';
        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(GameMatch::class);
    }

    public function players(): HasManyThrough
    {
        return $this->hasManyThrough(Player::class, Team::class);
    }

    public function playerStatistics(): HasMany
    {
        return $this->hasMany(PlayerStatistic::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getStatusColourAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ONGOING => 'success',
            self::STATUS_COMPLETED => 'secondary',
            default => 'info',
        };
    }
}
