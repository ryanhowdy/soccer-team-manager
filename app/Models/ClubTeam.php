<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\ClubType;

class ClubTeam extends Model
{
    use HasFactory;

    public function club(): HasOne
    {
        return $this->hasOne(Club::class, 'id', 'club_id');
    }

    //
    // Display accessors
    //

    /**
     * Is this team a high school team? Inherited from its club.
     *
     * @return bool
     */
    public function isSchoolTeam(): bool
    {
        // Queries that already join clubs select `c.type as club_type`, which
        // keeps the display accessors from firing a lookup per team. Fall back
        // to the relation when that column isn't present.
        if (array_key_exists('club_type', $this->attributes))
        {
            return $this->attributes['club_type'] === ClubType::School->value;
        }

        return (bool) $this->club?->isSchool();
    }

    /**
     * The club's name, without triggering a lookup where a query already
     * supplied it.
     *
     * Queries that join clubs alias it as `c.name as club_name`; everything else
     * falls back to the relation. Same fast path as isSchoolTeam().
     *
     * @return string
     */
    public function clubName(): string
    {
        if (array_key_exists('club_name', $this->attributes))
        {
            return (string) $this->attributes['club_name'];
        }

        return (string) ($this->club?->name ?? '');
    }

    /**
     * The team on its own, qualified by its cohort. Use where the club is
     * already obvious from context, or where space is tight - scoreboards, the
     * navbar pill, prose.
     *
     *   "Copa 2008"        "CW Varsity"
     *
     * @return string
     */
    public function getShortNameAttribute(): string
    {
        return trim($this->name . ' ' . $this->cohort_label);
    }

    /**
     * The fully qualified team, for pickers and any list that spans clubs.
     *
     *   "Pride SC: Copa 2008"     "Canal Winchester: CW Varsity"
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        $club = $this->clubName();

        return $club === ''
            ? $this->short_name
            : $club . ': ' . $this->short_name;
    }

    /**
     * The label identifying which cohort or level this team is, shown next to
     * the team name.
     *
     * A club team is a birth-year cohort, so the birth year identifies it. A
     * school team mixes ages, so its level does instead (Varsity / JV). This is
     * what replaced the bare `{{ $team->birth_year }}` in the team pickers once
     * birth_year became nullable.
     *
     * @return string
     */
    public function getCohortLabelAttribute(): string
    {
        if ($this->isSchoolTeam())
        {
            return $this->rank_label;
        }

        return (string) ($this->birth_year ?? '');
    }

    /**
     * Rank as the user should read it.
     *
     * The stored A/B/C/D value is never reinterpreted - existing club teams
     * already use it - only the label depends on the club type.
     *
     * @return string
     */
    public function getRankLabelAttribute(): string
    {
        if (!$this->isSchoolTeam())
        {
            return (string) ($this->rank ?? '');
        }

        // A high school fields at most three teams: varsity, JV and freshmen,
        // with the lower two optional. There is no fourth tier, so 'D' is not
        // offered for schools - it falls through to the raw value rather than
        // rendering blank, so pre-existing data is never hidden.
        return match ($this->rank)
        {
            'A'     => 'Varsity',
            'B'     => 'JV',
            'C'     => 'Freshmen',
            default => (string) ($this->rank ?? ''),
        };
    }

    /**
     * The other teams belonging to the same club.
     *
     * For a high school this is the varsity/JV stack, where the same player
     * commonly appears on more than one roster. For a club it is the other
     * birth-year cohorts, which do not share players - so callers that care
     * about roster overlap should check isSchoolTeam() first.
     *
     * @return HasMany
     */
    public function siblings(): HasMany
    {
        return $this->hasMany(ClubTeam::class, 'club_id', 'club_id')
            ->whereKeyNot($this->getKey());
    }

    public function latestHomeResults(): HasMany
    {
        return $this->hasMany(Result::class, 'home_team_id')
            ->orderBy('date', 'desc')
            ->where('status', '=', 'D')
            ->limit(5);
    }

    public function latestAwayResults(): HasMany
    {
        return $this->hasMany(Result::class, 'away_team_id')
            ->orderBy('date', 'desc')
            ->where('status', '=', 'D')
            ->limit(5);
    }

    public function latestResults()
    {
        return $this->latestHomeResults->merge($this->latestAwayResults)
            ->sortByDesc('date')
            ->take(5);
    }
}
