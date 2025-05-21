<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlayerPosition;

class PlayerPositionController extends Controller
{
    /**
     * destroy
     * 
     * @param PlayerPosition $playerPosition
     * @return json
     */
    public function destroy(PlayerPosition $playerPosition)
    {
        $playerPosition->delete();

        return response()->noContent();
    }
}
