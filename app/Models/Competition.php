<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    use HasFactory;

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    protected $appends = ['place_ordinal', 'level_percentage'];

    //
    // Attributes
    //

    public function getPlaceOrdinalAttribute(): string
    {
        $ordinal = addOrdinalNumberSuffix($this->attributes['place']);

        return $ordinal;
    }

    public function getLevelPercentageAttribute(): string
    {
        $parts = 0;

        if ($this->attributes['total_levels'] > 0)
        {
            $parts = round((100 / $this->attributes['total_levels']), 0);
        }

        $completed = $this->attributes['total_levels'] - $this->attributes['level'] + 1;

        return $completed * $parts;
    }
}
