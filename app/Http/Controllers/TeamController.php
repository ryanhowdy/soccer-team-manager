<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Club;

class TeamController extends Controller
{
    /**
     * Redirects to login or home page
     *
     * @return Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get all clubs
        $clubs = Club::with('teams')
            ->orderBy('name')
            ->get();

        return view('teams', [
            'clubs' => $clubs,
        ]);
    }
}
