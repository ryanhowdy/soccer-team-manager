<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PlayerPosition extends Model
{
    use HasFactory;

    // always return the position relationship
    protected $with = ['position'];

    // add a new column of position_name
    protected $appends = ['position_name'];

    public function position(): HasOne
    {
        return $this->hasOne(Position::class, 'id', 'position_id');
    }

    // this is how we get the 'position_name' column
    public function getPositionNameAttribute(): string
    {
        return $this->position->position;
    }
}
