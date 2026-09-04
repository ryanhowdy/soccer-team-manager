<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManagedPlayer;
use App\Models\Player;

class ManagedPlayerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $players = ManagedPlayer::where('user_id', Auth()->user()->id)
            ->with('player')
            ->get();

        return view('managed-players.index', [
            'players' => $players,
        ]);
    }

    /**
     * create
     * 
     * @return null
     */
    public function create()
    {
        // A high school player may have no birth year at all, so group by
        // whichever year identifies them rather than by birth_year alone -
        // otherwise they all collapse into one unlabeled group.
        $players = Player::orderByRaw('birth_year IS NULL')
            ->orderBy('birth_year')
            ->orderBy('graduation_year')
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($player) => $player->year_label ?: 'Unknown');

        return view('managed-players.create', [
            'players' => $players,
        ]);
    }

    /**
     * store
     * 
     * @param Request $request 
     * @return null
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'player_id' => 'required|exists:players,id',
        ]);

        $player = new ManagedPlayer;

        $player->user_id   = Auth()->user()->id;
        $player->player_id = $request->player_id;

        $player->save();

        return redirect()->route('settings');
    }
}
