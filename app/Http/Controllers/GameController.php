<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Result;
use App\Models\Season;
use App\Models\ClubTeam;
use App\Models\Competition;
use App\Models\ResultEvent;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\Event as EnumEvent;

class GameController extends Controller
{
    /**
     * index
     *
     * @return Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get all seasons
        $seasons = Season::all()->keyBy('id');

        // Get all non managed teams, group them by club
        $teams = ClubTeam::from('club_teams as t')
            ->select('t.*', 'c.name as club_name')
            ->join('clubs as c', 't.club_id', '=', 'c.id')
            ->whereNot('managed', 1)
            ->orderBy('club_name')
            ->orderBy('t.name')
            ->get()
            ->keyBy('id');

        $teamsByClub = [];
        foreach ($teams as $team)
        {
            $teamsByClub[$team->club_name][] = $team->toArray();
        }

        // Any filters
        $seasonId = $request->has('filter-seasons') ? $request->input('filter-seasons') : $seasons->keys()->last();
        $teamId   = $request->has('filter-teams')   ? $request->input('filter-teams')   : null;

        // Get all the results
        $query = Result::query()->where('status', 'D');

        if (!empty($seasonId))
        {
            $query->where('season_id', $seasonId);
        }

        if (!empty($teamId))
        {
            $query->where(function (Builder $q) use ($teamId) {
                $q->where('home_team_id', $teamId)
                    ->orWhere('away_team_id', $teamId);
            });
        }

        $results = $query->get();

        return view('games', [
            'selectedSeason' => $seasonId,
            'selectedTeam'   => $teamId,
            'seasons'        => $seasons,
            'teamsByClub'    => $teamsByClub,
            'results'        => $results,
        ]);
    }

    /**
     * preview
     *
     * @return Illuminate\View\View
     */
    public function preview(Request $request, $gameId)
    {
        $result = Result::find($gameId);

        $goodGuys = $result->homeTeam->managed ? 'home' : 'away';
        $badGuys  = $goodGuys == 'home'        ? 'away' : 'home';

        $goodGuysId = $result->{$goodGuys . '_team_id'};
        $badGuysId  = $result->{$badGuys . '_team_id'};

        // Get the last 5 games (all comps)
        $last5Results = Result::where('status', 'D')
            ->where(function (Builder $q) use ($goodGuysId) {
                $q->where('home_team_id', $goodGuysId)
                    ->orWhere('away_team_id', $goodGuysId);
            })
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        // Get all the head to head games
        $head2HeadResults = Result::where('status', 'D')
            ->where(function (Builder $q) use ($goodGuysId) {
                $q->where('home_team_id', $goodGuysId)
                    ->orWhere('away_team_id', $goodGuysId);
            })
            ->where(function (Builder $q) use ($badGuysId) {
                $q->where('home_team_id', $badGuysId)
                    ->orWhere('away_team_id', $badGuysId);
            })
            ->orderBy('date', 'desc')
            ->get();

        $counts = [
            'W' => 0,
            'D' => 0,
            'L' => 0,
        ];
        $resultIds = [];
        foreach ($head2HeadResults as $r)
        {
            $counts[$r->win_draw_loss]++;
            $resultIds[] = $r->id;
        }

        // Get all the events for all the head 2 head games
        $stats = [
            'good' => [
                'goals'     => 0,
                'shots'     => 0,
                'shots_on'  => 0,
                'shots_off' => 0,
                'corners'   => 0,
                'offsides'  => 0,
                'fouls'     => 0,
            ],
            'bad' => [
                'goals'     => 0,
                'shots'     => 0,
                'shots_on'  => 0,
                'shots_off' => 0,
                'corners'   => 0,
                'offsides'  => 0,
                'fouls'     => 0,
            ],
        ];
        $resultEvents = ResultEvent::whereIn('result_id', $resultIds)
            ->where('event_id', '!=', EnumEvent::start->value)
            ->orderBy('time')
            ->orderBy('id')
            ->get();

        foreach ($resultEvents as $e)
        {
            if ($e->event_id == EnumEvent::goal->value)
            {
                $stats['good']['goals']++;
                $stats['good']['shots']++;
                $stats['good']['shots_on']++;
            }
            if ($e->event_id == EnumEvent::shot_on_target->value)
            {
                $stats['good']['shots']++;
                $stats['good']['shots_on']++;
            }
            if ($e->event_id == EnumEvent::shot_off_target->value)
            {
                $stats['good']['shots']++;
                $stats['good']['shots_off']++;
            }
            if ($e->event_id == EnumEvent::goal_against->value)
            {
                $stats['bad']['goals']++;
                $stats['bad']['shots']++;
            }
            if ($e->event_id == EnumEvent::save->value)
            {
                $stats['bad']['shots']++;
                $stats['bad']['shots_on']++;
            }
            if ($e->event_id == EnumEvent::shot_against->value)
            {
                $stats['bad']['shots']++;
                $stats['bad']['shots_off']++;
            }
            if ($e->event_id == EnumEvent::corner_kick->value)
            {
                $stats['good']['corners']++;
            }
            if ($e->event_id == EnumEvent::corner_kick_against->value)
            {
                $stats['bad']['corners']++;
            }
            if ($e->event_id == EnumEvent::fouled->value)
            {
                $stats['good']['fouls']++;
            }
            if ($e->event_id == EnumEvent::foul->value)
            {
                $stats['bad']['fouls']++;
            }
        }

        return view('games.preview', [
            'result'           => $result,
            'goodGuys'         => $goodGuys,
            'badGuys'          => $badGuys,
            'counts'           => $counts,
            'last5Results'     => $last5Results,
            'head2HeadResults' => $head2HeadResults,
            'stats'            => $stats,
        ]);
    }
}
