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
            ->select('t.*', 'c.name as club_name')
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
