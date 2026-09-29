<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Raid extends Model
{
    use HasFactory;

    public const RESULT_SUCCESSFUL = 'successful';
    public const RESULT_UNSUCCESSFUL = 'unsuccessful';
    public const RESULT_EMPTY = 'empty';

    public const RESULTS = [
        self::RESULT_SUCCESSFUL => 'Successful',
        self::RESULT_UNSUCCESSFUL => 'Unsuccessful',
        self::RESULT_EMPTY => 'Empty',
    ];

    protected $fillable = [
        'match_id',
        'raider_id',
        'raiding_team_id',
        'defending_team_id',
        'raid_number',
        'result',
        'touch_points',
        'bonus_points',
        'raid_points',
        'defending_points',
        'is_bonus',
        'is_super_raid',
        'is_do_or_die',
        'half',
        'game_clock_at_event',
        'raid_duration_seconds',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'touch_points' => 'integer',
            'bonus_points' => 'integer',
            'raid_points' => 'integer',
            'defending_points' => 'integer',
            'raid_number' => 'integer',
            'half' => 'integer',
            'game_clock_at_event' => 'integer',
            'raid_duration_seconds' => 'integer',
            'is_bonus' => 'boolean',
            'is_super_raid' => 'boolean',
            'is_do_or_die' => 'boolean',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

    public function raider(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'raider_id');
    }

    public function raidingTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'raiding_team_id');
    }

    public function defendingTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'defending_team_id');
    }

    public function defensiveActions(): HasMany
    {
        return $this->hasMany(DefensiveAction::class);
    }

    public function getResultLabelAttribute(): string
    {
        return self::RESULTS[$this->result] ?? ucfirst((string) $this->result);
    }

    public function getResultColourAttribute(): string
    {
        return match ($this->result) {
            self::RESULT_SUCCESSFUL => 'success',
            self::RESULT_UNSUCCESSFUL => 'danger',
            default => 'secondary',
        };
    }
}
