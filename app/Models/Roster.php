<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Roster extends Model
{
    use HasFactory;

    // always return the position relationship
    protected $with = ['player'];

    //
    // Relationships
    //

    public function player(): HasOne
    {
        return $this->hasOne(Player::class, 'id', 'player_id');
    }

    public function clubTeamSeason(): HasOne
    {
        return $this->hasOne(ClubTeamSeason::class, 'id', 'club_team_season_id');
    }
}
