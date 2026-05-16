<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameSession extends Model
{
    protected $fillable = [
        'activity_id',
        'game_resource_id',
        'status',
        'duration_minutes',
        'started_at',
        'ends_at',
        'ended_at',
        'share_code',
        'share_expires_at',
        'temporary_assets_expire_at',
        'cleanup_completed_at',
        'winner_team_name',
        'team_one_total',
        'team_two_total',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
        'ended_at' => 'datetime',
        'share_expires_at' => 'datetime',
        'temporary_assets_expire_at' => 'datetime',
        'cleanup_completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(GameResource::class, 'game_resource_id');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(GameTeam::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(GamePlayer::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(GameRound::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(GameScore::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['setup', 'playing', 'paused'], true);
    }

    public function shareHasExpired(): bool
    {
        return $this->share_expires_at !== null && now()->greaterThan($this->share_expires_at);
    }
}