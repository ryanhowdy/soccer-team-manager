<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ClubTeam;

/**
 * One-off cleanup. Team names carry their own cohort ("Copa 08",
 * "CW (Varsity)") because club_teams.birth_year was added long after those names
 * were entered, and the naming convention simply continued. Now that
 * ClubTeam::$short_name and ClubTeam::$display_name append the cohort, keeping it
 * in the name too renders it twice ("Copa 08 2008").
 *
 * Run with --pretend first. Nothing is written in that mode.
 */
class StripTeamCohort extends Command
{
    protected $signature = 'app:strip-team-cohort
                            {--pretend : Show every proposed change and write nothing}';

    protected $description = 'Remove the cohort (birth year / varsity-JV tier) from club_teams.name, now that it is appended for display';

    /** Tier words a high school name might carry. Longest first so JVA/JV2 win over JV. */
    private const TIERS = ['Varsity', 'Freshmen', 'Freshman', 'JVA', 'JV2', 'JV'];

    public function handle(): int
    {
        $pretend = (bool) $this->option('pretend');

        $teams = ClubTeam::with('club')->orderBy('club_id')->orderBy('name')->get();

        $changes = [];
        $skipped = [];

        foreach ($teams as $team)
        {
            $proposed = $this->strip($team);

            if ($proposed === null)
            {
                continue; // nothing to strip
            }

            $reason = $this->unsafeReason($team, $proposed);

            if ($reason !== null)
            {
                $skipped[] = [$team->id, $this->clubOf($team), $team->name, $proposed ?: '(empty)', $reason];
                continue;
            }

            $changes[] = [
                'team'     => $team,
                'from'     => $team->name,
                'to'       => $proposed,
                'preview'  => $this->clubOf($team) . ': ' . trim($proposed . ' ' . $team->cohort_label),
            ];
        }

        $collisions = $this->collisions($changes);

        $this->report($changes, $skipped, $collisions, $teams->count());

        if (empty($changes))
        {
            $this->info('Nothing to change.');
            return self::SUCCESS;
        }

        if ($pretend)
        {
            $this->newLine();
            $this->warn('--pretend: no changes written. Re-run without --pretend to apply.');
            return self::SUCCESS;
        }

        if (!$this->confirm(sprintf('Rename %d team(s)?', count($changes)), false))
        {
            $this->info('Aborted; nothing written.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($changes) {
            foreach ($changes as $c)
            {
                // Only the name changes; updated_user_id is left alone because a
                // CLI run has no authenticated user to attribute it to.
                $c['team']->name = $c['to'];
                $c['team']->save();
            }
        });

        $this->info(sprintf('Renamed %d team(s).', count($changes)));

        return self::SUCCESS;
    }

    /**
     * The name with its cohort removed, or null if there was nothing to remove.
     */
    private function strip(ClubTeam $team): ?string
    {
        $name = $team->name;

        // The birth year, as a whole token in either form. \b keeps "08" from
        // matching inside "2008", and keeps "2011" from matching "2011B" - names
        // where the digits are fused to something else are left alone on purpose.
        if (!empty($team->birth_year))
        {
            $year = (string) $team->birth_year;

            $name = preg_replace('/\b' . preg_quote($year, '/') . '\b/', ' ', $name);
            $name = preg_replace('/\b' . preg_quote(substr($year, 2), '/') . '\b/', ' ', $name);
        }

        // A school team's cohort is its tier, which is usually parenthesised
        if ($team->isSchoolTeam())
        {
            foreach (self::TIERS as $tier)
            {
                $name = preg_replace('/\(\s*' . $tier . '\s*\)/i', ' ', $name);
                $name = preg_replace('/\b' . $tier . '\b/i', ' ', $name);
            }
        }

        $name = $this->tidy($name);

        return $name === $team->name ? null : $name;
    }

    /**
     * Clean up what removing a token leaves behind - empty brackets, doubled
     * spaces, and separators now dangling at either end.
     */
    private function tidy(string $name): string
    {
        $name = preg_replace('/\(\s*\)/', ' ', $name);      // "Grey () 08" -> "Grey  "
        $name = preg_replace('/\s+/', ' ', $name);           // collapse runs
        $name = preg_replace('/\s+([)\]])/', '$1', $name);   // " )" -> ")"
        $name = preg_replace('/([(\[])\s+/', '$1', $name);   // "( " -> "("
        $name = trim($name, " \t-–—/,:;&+");

        return trim($name);
    }

    /**
     * Why this rename should not be made automatically, or null if it is fine.
     */
    private function unsafeReason(ClubTeam $team, string $proposed): ?string
    {
        if ($proposed === '')
        {
            return 'name was only the cohort';
        }

        // Single characters are legitimate team names here - "I" and "II" are a
        // series (Sporting Columbus), and "B" is short for Boys. What is not
        // legitimate is a leftover fragment with nothing readable in it, such as
        // an unbalanced bracket the tidy pass could not resolve.
        if (!preg_match('/[\p{L}\p{N}]/u', $proposed))
        {
            return 'nothing but punctuation left';
        }

        // Names are not unique in this schema, and display_name still separates
        // them by cohort - but flag it so the collision is a decision, not a
        // surprise.
        $clash = ClubTeam::where('club_id', $team->club_id)
            ->where('id', '!=', $team->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($proposed)])
            ->exists();

        if ($clash)
        {
            return 'another team in this club is already named that';
        }

        return null;
    }

    /**
     * Renames that would end up sharing a name with a sibling team.
     *
     * unsafeReason() only sees names as they are now, so it cannot catch
     * "Copa 08" and "Copa 11" both becoming "Copa". These are reported rather
     * than skipped: club_teams.name has no unique constraint and display_name
     * still separates them by cohort ("Copa 2008" vs "Copa 2011"), so this is a
     * judgement call rather than an error.
     */
    private function collisions(array $changes): array
    {
        $byClub = [];

        foreach ($changes as $c)
        {
            $key = $c['team']->club_id . '|' . mb_strtolower($c['to']);
            $byClub[$key][] = $c;
        }

        $out = [];

        foreach ($byClub as $group)
        {
            if (count($group) < 2)
            {
                continue;
            }

            foreach ($group as $c)
            {
                $out[] = [
                    $c['team']->id,
                    $this->clubOf($c['team']),
                    $c['from'],
                    $c['to'],
                    $c['preview'],
                ];
            }
        }

        return $out;
    }

    private function clubOf(ClubTeam $team): string
    {
        return $team->club->name ?? '(no club)';
    }

    private function report(array $changes, array $skipped, array $collisions, int $total): void
    {
        $this->info(sprintf('Examined %d team(s).', $total));
        $this->newLine();

        if (!empty($changes))
        {
            $this->line(sprintf('<info>%d rename(s):</info>', count($changes)));
            $this->table(
                ['id', 'club', 'name now', 'name after', 'displays as'],
                array_map(fn ($c) => [
                    $c['team']->id,
                    $this->clubOf($c['team']),
                    $c['from'],
                    $c['to'],
                    $c['preview'],
                ], $changes)
            );
        }

        if (!empty($collisions))
        {
            $this->newLine();
            $this->line(sprintf(
                '<comment>%d of those will share a name with a sibling team (still distinct via display_name):</comment>',
                count($collisions)
            ));
            $this->table(['id', 'club', 'name now', 'name after', 'displays as'], $collisions);
        }

        if (!empty($skipped))
        {
            $this->newLine();
            $this->line(sprintf('<comment>%d skipped (needs a human):</comment>', count($skipped)));
            $this->table(['id', 'club', 'name now', 'would become', 'why skipped'], $skipped);
        }
    }
}
