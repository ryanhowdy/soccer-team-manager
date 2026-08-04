<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use HasFactory;

    protected $appends = ['season_year'];

    //
    // Scopes
    //

    /**
     * Most recent season first.
     *
     * Ids are assigned chronologically, so ordering by id within a year puts
     * Fall ahead of Spring without having to sort on the season name (which
     * sorts alphabetically, not chronologically).
     */
    public function scopeNewestFirst($query)
    {
        return $query->orderBy('year', 'desc')->orderBy('id', 'desc');
    }

    //
    // Attributes
    //

    public function getSeasonYearAttribute(): string
    {
        return $this->attributes['season'] . ' ' . $this->attributes['year'];
    }
}
