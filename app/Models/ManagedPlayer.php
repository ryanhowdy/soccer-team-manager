<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ManagedPlayer extends Model
{
    public function player(): HasOne
    {
        return $this->hasOne(Player::class, 'id', 'player_id');
    }
}
