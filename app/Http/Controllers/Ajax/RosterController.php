<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Roster;

class RosterController extends Controller
{
    /**
     * destroy
     * 
     * @param Roster $roster
     * @return json
     */
    public function destroy(Roster $roster)
    {
        $roster->delete();

        return response()->noContent();
    }
}
