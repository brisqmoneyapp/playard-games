<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'resource_label',
        'is_active',
        'how_to_play',
        'rules',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rules' => 'array',
    ];

    public function resources(): HasMany
    {
        return $this->hasMany(GameResource::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }
}