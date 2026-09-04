<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Club;

class OpponentController extends Controller
{
    /**
     * index
     *
     * The clubs we play against — everything that isn't one of ours. This was the
     * "Teams & Clubs" page; the managed half moved to TeamController@index.
     *
     * @param Request $request
     * @return Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get all clubs
        // teams.club looks redundant, but it lets the ClubTeam display accessors
        // (cohort_label / rank_label) read the club type without firing a lookup
        // per team - Eloquent resolves it in one extra query.
        $clubs = Club::with('teams.club')
            ->orderBy('name')
            ->get();

        return view('opponents.index', [
            // Any club with a team we don't manage.  A club with a mix shows on
            // both this page and Teams, so its unmanaged teams stay reachable here
            // rather than being hidden behind the managed half.
            'opponentClubs'    => $clubs->filter(fn ($club) => clubListsAsOpponent($club)),
            // The create-team form offers every club, not just opponents
            'clubs'            => $clubs,
            'createTeamAction' => route('teams.store'),
            'createClubAction' => route('clubs.store'),
        ]);
    }
}
