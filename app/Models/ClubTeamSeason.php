<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClubTeamSeason extends Model
{
    use HasFactory;

    public function clubTeam(): HasOne
    {
        return $this->hasOne(ClubTeam::class, 'id', 'club_team_id');
    }

    public function season(): HasOne
    {
        return $this->hasOne(Season::class, 'id', 'season_id');
    }
}
