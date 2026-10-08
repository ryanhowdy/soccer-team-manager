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
     * Three-letter label for tight spaces, like the live game scoreboard on a
     * phone: the first three letters of the club name.
     *
     *   "Pride SC" => "PRI"      "FC Dayton" => "FCD"
     *
     * @return string
     */
    public function getAbbreviationAttribute(): string
    {
        return strtoupper(mb_substr(preg_replace('/[^A-Za-z0-9]/', '', $this->name), 0, 3));
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
