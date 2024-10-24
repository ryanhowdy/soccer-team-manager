<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ResultEvent extends Model
{
    use HasFactory;

    protected $with = ['event', 'player', 'additionalPlayer'];

    protected $appends = ['event_name', 'player_name'];

    //
    // Relationships
    //

    public function event(): HasOne
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }

    public function player(): HasOne
    {
        return $this->hasOne(Player::class, 'id', 'player_id');
    }

    public function additionalplayer(): HasOne
    {
        return $this->hasOne(Player::class, 'id', 'additional');
    }

    //
    // Attributes
    //

    public function getEventNameAttribute(): string
    {
        return $this->event->event;
    }

    public function getPlayerNameAttribute(): string
    {
        return $this->player ? $this->player->name : 'Unknown';
    }
}
