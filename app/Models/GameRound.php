<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameRound extends Model
{
    protected $fillable = [
        'game_session_id',
        'round_number',
        'winning_team_id',
        'points',
        'commentary',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'game_session_id');
    }

    public function winningTeam(): BelongsTo
    {
        return $this->belongsTo(GameTeam::class, 'winning_team_id');
    }
}