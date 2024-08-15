<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Result extends Model
{
    use HasFactory;

    protected $casts = [
        'date' => 'datetime',
    ];

    protected $with = ['homeTeam.club', 'awayTeam.club'];

    protected $appends = ['win_draw_loss'];

    //
    // Relationships
    //

    public function season(): HasOne
    {
        return $this->hasOne(Season::class);
    }

    public function competition(): HasOne
    {
        return $this->hasOne(Competition::class, 'id', 'competition_id');
    }

    public function location(): HasOne
    {
        return $this->hasOne(Location::class, 'id', 'location_id');
    }

    public function homeTeam(): HasOne
    {
        return $this->hasOne(ClubTeam::class, 'id', 'home_team_id');
    }

    public function awayTeam(): HasOne
    {
        return $this->hasOne(ClubTeam::class, 'id', 'away_team_id');
    }

    public function formation(): HasOne
    {
        return $this->hasOne(Formation::class, 'id', 'formation_id');
    }

    //
    // Attributes
    //

    public function getWinDrawLossAttribute(): string
    {
        if ($this->attributes['home_team_score'] > $this->attributes['away_team_score'])
        {
            return $this->homeTeam->managed ? 'W' : 'L';
        }
        if ($this->attributes['home_team_score'] < $this->attributes['away_team_score'])
        {
            return $this->homeTeam->managed ? 'L' : 'W';
        }
        if ($this->attributes['home_team_score'] == $this->attributes['away_team_score'])
        {
            return 'D';
        }
    }
}
