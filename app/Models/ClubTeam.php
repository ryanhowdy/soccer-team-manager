<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClubTeam extends Model
{
    use HasFactory;

    public function club(): HasOne
    {
        return $this->hasOne(Club::class, 'id', 'club_id');
    }

    public function latestHomeResults(): HasMany
    {
        return $this->hasMany(Result::class, 'home_team_id')
            ->orderBy('date', 'desc')
            ->where('status', '=', 'D')
            ->limit(5);
    }

    public function latestAwayResults(): HasMany
    {
        return $this->hasMany(Result::class, 'away_team_id')
            ->orderBy('date', 'desc')
            ->where('status', '=', 'D')
            ->limit(5);
    }

    public function latestResults()
    {
        return $this->latestHomeResults->merge($this->latestAwayResults)
            ->sortByDesc('date')
            ->take(5);
    }
}
