<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ClubTeam;
use Symfony\Component\HttpFoundation\Response;

class LoadSelectedTeam
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user)
        {
            // If no team selected yet, pick the first managed team
            if (!$user->selected_club_team_id)
            {
                $firstManaged = ClubTeam::where('managed', 1)->first();

                if ($firstManaged)
                {
                    $user->update(['selected_club_team_id' => $firstManaged->id]);
                }
            }

            // Eager-load the relationship for the rest of the request
            $user->load('selectedTeam');
        }

        return $next($request);
    }
}
