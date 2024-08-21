<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Formation;
use App\Rules\FormationString;

class FormationController extends Controller
{
    /**
     * index
     *
     * @return Illuminate\View\View
     */
    public function index(Request $request)
    {
        $formations = Formation::all()
            ->groupBy('players');

        return view('formations.index', [
            'formations' => $formations,
            'action'     => route('formations.store'),
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
            'players'   => 'required|in:7,9,11',
            'name'      => 'required|string|max:255',
            'formation' => [
                'required',
                'string',
                'max:255',
                new FormationString
            ],
        ]);

        $json = [];

        $noWhitespace = str_replace(' ', '', $request->formation);

        preg_match_all('/(^.+$)/m', $noWhitespace, $matches);

        foreach ($matches[0] as $row)
        {
            $array = explode(',', trim($row));

            foreach ($array as $i => $k)
            {
                $array[$i] = strtoupper($k);
            }

            $json[] = $array;
        }

        $json[] = ['G'];

        $formation = new Formation;

        $formation->players         = $request->players;
        $formation->name            = $request->name;
        $formation->formation       = json_encode($json);
        $formation->created_user_id = Auth()->user()->id;
        $formation->updated_user_id = Auth()->user()->id;

        $formation->save();

        return redirect()->route('formations.index');
    }
}
