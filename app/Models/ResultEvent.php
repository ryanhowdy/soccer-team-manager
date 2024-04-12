<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ResultEvent extends Model
{
    use HasFactory;

    // always return the event and player relationship
    protected $with = ['event', 'player'];

    // add new columns: event_name, player_name
    protected $appends = ['event_name', 'player_name'];

    public function event(): HasOne
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }

    public function player(): HasOne
    {
        return $this->hasOne(Player::class, 'id', 'player_id');
    }

    // this is how we get the 'event_name' column
    public function getEventNameAttribute(): string
    {
        return $this->event->event;
    }

    // this is how we get the 'player_name' column
    public function getPlayerNameAttribute(): string
    {
        return $this->player->name;
    }
}
