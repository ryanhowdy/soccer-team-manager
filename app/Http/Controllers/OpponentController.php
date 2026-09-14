<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\ClubType;
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
        $selectedTypes = $this->resolveTypeFilter($request);

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
            'opponentClubs'    => $clubs->filter(fn ($club) => clubListsAsOpponent($club)
                && in_array($club->type, $selectedTypes, true)),
            // The create-team form offers every club, not just opponents
            'clubs'            => $clubs,
            'selectedTypes'    => $selectedTypes,
            'createTeamAction' => route('teams.store'),
            'createClubAction' => route('clubs.store'),
        ]);
    }

    /**
     * resolveTypeFilter
     *
     * Which club types the opponent list shows.
     *
     * The default follows the team in the navbar picker, because that is what
     * you almost always want: looking at a high school team, you are looking
     * for high school opponents.  Both switches stay available for the times
     * you are not - hunting one specific club, or reaching a club that was
     * given the wrong type.
     *
     * An explicit choice is remembered, but only for the team it was made
     * under.  Switch team and the default takes over again, rather than
     * carrying a club-team filter into a high school team's opponents.
     *
     * @param Request $request
     * @return array
     */
    private function resolveTypeFilter(Request $request): array
    {
        $selectedTeam = Auth()->user()->selectedTeam;
        $teamId       = $selectedTeam?->id;

        $allTypes = array_column(ClubType::cases(), 'value');

        // Came from the switches.  The hidden field is what separates "both
        // unticked" from a plain visit - unticked boxes post nothing at all.
        if ($request->has('filtered'))
        {
            $types = array_values(array_intersect($allTypes, (array) $request->input('types', [])));

            session(['opponent_types' => ['team_id' => $teamId, 'types' => $types]]);

            return $types;
        }

        $remembered = session('opponent_types');

        if (is_array($remembered) && $remembered['team_id'] === $teamId)
        {
            return $remembered['types'];
        }

        if ($selectedTeam)
        {
            return [$selectedTeam->isSchoolTeam() ? ClubType::School->value : ClubType::Club->value];
        }

        // No team picked yet - nothing to infer from, so show everything.
        return $allTypes;
    }
}
