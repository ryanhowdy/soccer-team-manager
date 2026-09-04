<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\ClubType;

class Club extends Model
{
    use HasFactory;

    public function teams(): HasMany
    {
        return $this->hasMany(ClubTeam::class);
    }

    /**
     * Is this club a high school?
     *
     * The one place the club/school distinction should be read from. Everything
     * that behaves differently for high school teams keys off this rather than
     * comparing the raw column.
     *
     * @return bool
     */
    public function isSchool(): bool
    {
        return $this->type === ClubType::School->value;
    }
}
