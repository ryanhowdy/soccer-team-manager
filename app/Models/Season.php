<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use HasFactory;

    protected $appends = ['season_year'];

    //
    // Attributes
    //

    public function getSeasonYearAttribute(): string
    {
        return $this->attributes['season'] . ' ' . $this->attributes['year'];
    }
}
