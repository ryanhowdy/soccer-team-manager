<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    use HasFactory;

    public function teams(): HasMany
    {
        return $this->hasMany(PlayerTeam::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(PlayerPosition::class);
    }

    public function rosters(): HasMany
    {
        return $this->hasMany(Roster::class);
    }

    public function currentRoster(): HasOne
    {
        return $this->hasOne(Roster::class)->latestOfMany();
    }
}
