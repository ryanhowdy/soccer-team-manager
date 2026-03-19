<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlayerGameRating;
use App\Models\Result;

class PlayerGameRatingController extends Controller
{
    /**
     * store
     *
     * @param Result  $result
     * @param Request $request
     * @return json
     */
    public function store(Result $result, Request $request)
    {
        $validated = $request->validate([
            'player_id' => 'required|exists:players,id',
            'rating'    => 'required|numeric|min:0|max:10',
        ]);

        $rating = PlayerGameRating::updateOrCreate(
            [
                'result_id'       => $result->id,
                'player_id'       => $request->player_id,
                'created_user_id' => auth()->id(),
            ],
            [
                'rating' => round($request->rating, 1),
            ]
        );

        // Return the updated average for this player in this game
        $avg = PlayerGameRating::where('result_id', $result->id)
            ->where('player_id', $request->player_id)
            ->avg('rating');

        return response()->json([
            'success' => true,
            'average' => round($avg, 1),
        ], 200);
    }
}
