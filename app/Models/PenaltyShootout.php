<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenaltyShootout extends Model
{
    public function penalties(): HasMany
    {
        return $this->hasMany(Penalty::class);
    }
}
