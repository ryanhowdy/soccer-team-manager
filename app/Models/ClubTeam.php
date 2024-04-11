<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClubTeam extends Model
{
    use HasFactory;

    public function club(): HasOne
    {
        return $this->hasOne(Club::class, 'id', 'club_id');
    }
}
