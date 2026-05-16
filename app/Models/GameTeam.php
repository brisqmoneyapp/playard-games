<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameTeam extends Model
{
    protected $fillable = [
        'game_session_id',
        'name',
        'colour',
        'total_score',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'game_session_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(GamePlayer::class);
    }
}