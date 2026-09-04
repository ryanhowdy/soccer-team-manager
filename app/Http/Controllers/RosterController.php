<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use App\Models\PlayerTeam;
use App\Models\ClubTeamSeason;
use App\Models\ClubTeam;
use App\Models\Season;
use App\Models\Roster;
use App\Models\Position;
use App\Models\Player;
use App\Models\ManagedPlayer;

class RosterController extends Controller
{
    /**
     * index
     *
     * The unified, team-scoped roster page: the selected team's squad for a
     * chosen season (per-page filter, defaulting to the latest season). Merges
     * what used to be the season-first Rosters page and the Players page.
     *
     * @return Illuminate\View\View
     */
    public function index(Request $request)
    {
        // All managed teams (needed for the picker + Add Player modal)
        $managedTeams = ClubTeam::from('club_teams as t')
            ->select('t.*', 'c.name as club_name', 'c.type as club_type')
            ->join('clubs as c', 't.club_id', '=', 'c.id')
            ->where('managed', 1)
            ->orderBy('club_name')
            ->orderBy('t.name')
            ->with('club')
            ->get();

        if ($managedTeams->isEmpty())
        {
            return redirect()->route('clubs.first');
        }

        // The team to display is the globally selected team (navbar picker),
        // falling back to the first managed team.
        $selectedTeam = auth()->user()->selectedTeam;

        if (!$selectedTeam || !$managedTeams->contains('id', $selectedTeam->id))
        {
            $selectedTeam = $managedTeams->first();
        }

        // Seasons for the filter (newest first). A roster belongs to a single
        // team-season, so "All Seasons" isn't offered here.
        $seasons = Season::newestFirst()->get()->keyBy('id');

        $selectedSeason = $seasons->isEmpty()
            ? null
            : resolveSeasonFilter($request, $seasons, allowAll: false);

        // The team-season link (may not exist yet for this team/season)
        $clubTeamSeason = null;
        if ($selectedSeason)
        {
            $clubTeamSeason = ClubTeamSeason::where('club_team_id', $selectedTeam->id)
                ->where('season_id', $selectedSeason->id)
                ->first();
        }

        // Players rostered for this team + season
        $rosterPlayers = collect();
        if ($clubTeamSeason)
        {
            $rosterPlayers = Roster::where('club_team_season_id', $clubTeamSeason->id)
                ->with('player.positions')
                ->get()
                ->sortBy(fn ($r) => optional($r->player)->name)
                ->values();
        }

        // Players in this team's pool who aren't on the season roster yet
        $rosteredPlayerIds = $rosterPlayers->pluck('player_id')->filter()->all();

        $availablePlayers = PlayerTeam::from('player_teams as pt')
            ->select('p.id', 'p.name')
            ->join('players as p', 'pt.player_id', '=', 'p.id')
            ->where('pt.club_team_id', $selectedTeam->id)
            ->whereNotIn('p.id', $rosteredPlayerIds ?: [0])
            ->orderBy('p.name')
            ->get();

        // Varsity/JV overlap: a high school player commonly plays on both
        // rosters, so flag it rather than letting it read as a duplicate entry.
        // Club teams under one club are independent birth-year cohorts that do
        // not share players, so this is deliberately school-only.
        $alsoRosteredOn = [];

        if ($clubTeamSeason && !empty($rosteredPlayerIds) && $selectedTeam->isSchoolTeam())
        {
            $siblings = $selectedTeam->siblings()->with('club')->get()->keyBy('id');

            if ($siblings->isNotEmpty())
            {
                // Raw rows on purpose - the Roster model eager-loads `player`
                // globally, which this doesn't need.
                $overlaps = DB::table('rosters as r')
                    ->select('r.player_id', 'cts.club_team_id')
                    ->join('club_team_seasons as cts', 'r.club_team_season_id', '=', 'cts.id')
                    ->where('cts.season_id', $selectedSeason->id)
                    ->whereIn('cts.club_team_id', $siblings->keys())
                    ->whereIn('r.player_id', $rosteredPlayerIds)
                    ->get();

                foreach ($overlaps as $overlap)
                {
                    $sibling = $siblings[$overlap->club_team_id] ?? null;

                    if ($sibling)
                    {
                        $alsoRosteredOn[$overlap->player_id][] = $sibling->rank_label ?: $sibling->name;
                    }
                }
            }
        }

        // The user's favourites, so the roster can flag them. Same shape as
        // StatsLineupController uses. players.managed was dropped in migration
        // 0.7.0 and replaced by managed_players; this view was still reading the
        // dead column, so the flag never rendered.
        $managedPlayerIds = ManagedPlayer::where('user_id', auth()->user()->id)
            ->pluck('player_id')
            ->flip()
            ->toArray();

        // Inline position editing + Add Player modal
        $positions  = Position::orderBy('position')->get();
        $allPlayers = Player::orderBy('name')->get();

        return view('rosters.index', [
            'managedTeams'     => $managedTeams,
            'selectedTeam'     => $selectedTeam,
            'seasons'          => $seasons,
            'selectedSeason'   => $selectedSeason,
            'clubTeamSeason'   => $clubTeamSeason,
            'rosterPlayers'    => $rosterPlayers,
            'managedPlayerIds' => $managedPlayerIds,
            'alsoRosteredOn'   => $alsoRosteredOn,
            'availablePlayers' => $availablePlayers,
            'positions'        => $positions,
            'allPlayers'       => $allPlayers,
            'action'           => route('players.store'),
        ]);
    }

    /**
     * update
     *
     * @param Roster $id
     * @param Request $request
     * @return Illuminate\View\View
     */
    public function update(Roster $roster, Request $request)
    {
        $validated = $request->validate([
            'club_team_season_id' => 'required|exists:club_team_seasons,id',
            'player_id'           => 'required|integer|exists:players,id',
            'number' => [
                'nullable',
                'integer',
                Rule::unique('rosters', 'number')
                    ->where(fn (Builder $query) => $query->where('club_team_season_id', $request->club_team_season_id)),
            ],
        ]);

        $roster->club_team_season_id = $request->club_team_season_id;
        $roster->player_id           = $request->player_id;
        $roster->updated_user_id     = Auth()->user()->id;

        if ($request->filled('number'))
        {
            $roster->number = $request->number;
        }

        $roster->save();

        return redirect()->route('rosters.index');
    }
}
