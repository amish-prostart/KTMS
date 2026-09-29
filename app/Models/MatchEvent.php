<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchEvent extends Model
{
    use HasFactory;

    public const TYPE_SCORE_ADJUSTMENT = 'score_adjustment';
    public const TYPE_RAID = 'raid';
    public const TYPE_DEFENSE = 'defense';
    public const TYPE_RAID_STARTED = 'raid_started';
    public const TYPE_RAID_TOGGLED = 'raid_toggled';
    public const TYPE_TIMER = 'timer';
    public const TYPE_HALF_CHANGE = 'half_change';
    public const TYPE_COURT_UPDATE = 'court_update';
    public const TYPE_STATUS_CHANGE = 'status_change';
    public const TYPE_ALL_OUT = 'all_out';

    protected $fillable = [
        'match_id',
        'team_id',
        'player_id',
        'raid_id',
        'event_type',
        'description',
        'points',
        'half',
        'game_clock_at_event',
        'payload',
        'is_undone',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'points' => 'integer',
            'half' => 'integer',
            'game_clock_at_event' => 'integer',
            'is_undone' => 'boolean',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function raid(): BelongsTo
    {
        return $this->belongsTo(Raid::class);
    }

    public function getClockLabelAttribute(): string
    {
        return $this->game_clock_at_event === null
            ? '--:--'
            : GameMatch::formatClock($this->game_clock_at_event);
    }

    public function getIconAttribute(): string
    {
        return match ($this->event_type) {
            self::TYPE_RAID => 'bi-person-running',
            self::TYPE_DEFENSE => 'bi-shield-check',
            self::TYPE_ALL_OUT => 'bi-exclamation-octagon',
            self::TYPE_TIMER => 'bi-stopwatch',
            self::TYPE_HALF_CHANGE => 'bi-arrow-repeat',
            self::TYPE_SCORE_ADJUSTMENT => 'bi-plus-slash-minus',
            default => 'bi-record-circle',
        };
    }
}
