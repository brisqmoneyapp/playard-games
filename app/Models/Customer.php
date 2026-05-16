<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'marketing_consent',
        'marketing_consent_at',
        'marketing_source',
        'last_game_at',
        'games_played',
    ];

    protected $casts = [
        'marketing_consent' => 'boolean',
        'marketing_consent_at' => 'datetime',
        'last_game_at' => 'datetime',
    ];

    public function getNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')) ?: $this->email;
    }
}