<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PlayerGameRating extends Model
{
    use HasFactory;

    protected $fillable = ['result_id', 'player_id', 'created_user_id', 'rating'];

    //
    // Relationships
    //

    public function result(): HasOne
    {
        return $this->hasOne(Result::class, 'id', 'result_id');
    }

    public function player(): HasOne
    {
        return $this->hasOne(Player::class, 'id', 'player_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_user_id');
    }
}
