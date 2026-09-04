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

    /**
     * The year that identifies this player in a list or picker.
     *
     * A club player is identified by birth year. A high school player may only
     * have a graduation year, since a school team mixes ages and birth year
     * carries no useful information there.
     *
     * @return string
     */
    public function getYearLabelAttribute(): string
    {
        if (!empty($this->birth_year))
        {
            return (string) $this->birth_year;
        }

        if (!empty($this->graduation_year))
        {
            return 'Class of ' . $this->graduation_year;
        }

        return '';
    }
}
