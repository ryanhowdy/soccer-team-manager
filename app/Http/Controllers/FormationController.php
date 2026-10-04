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
        // players is an enum, which MySQL sorts by declaration order, so the
        // biggest-first tab order (11v11, 9v9, 7v7) is applied to the groups here
        $formations = Formation::orderBy('name')
            ->orderBy('description')
            ->get()
            ->groupBy('players')
            ->sortKeysDesc();

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
            'players'     => 'required|in:7,9,11',
            'name'        => [
                'required',
                'regex:/^[1-9]+$/',
                'max:255',
                // Each digit is a row of outfield players, so the digits have
                // to add up to the player count minus the goalie (433 → 10 → 11v11)
                function ($attribute, $value, $fail) use ($request) {
                    if (!preg_match('/^[1-9]+$/', $value) || !is_numeric($request->players)) {
                        return;
                    }

                    $outfield = array_sum(str_split($value));
                    $expected = $request->players - 1;

                    if ($outfield != $expected) {
                        $fail("The name's digits must add up to {$expected} for {$request->players}v{$request->players} (" . implode('+', str_split($value)) . " = {$outfield}).");
                    }
                },
            ],
            'description' => 'nullable|string|max:255',
            'formation' => [
                'required',
                'string',
                'max:255',
                new FormationString
            ],
        ], [
            'name.regex' => 'The name may only contain the digits 1-9, one per row of the formation (e.g. 433).',
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
        $formation->description     = $request->description;
        $formation->formation       = json_encode($json);
        $formation->created_user_id = Auth()->user()->id;
        $formation->updated_user_id = Auth()->user()->id;

        $formation->save();

        return redirect()->route('formations.index');
    }

    /**
     * update
     *
     * @param Formation $formation
     * @param Request $request
     * @return null
     */
    public function update(Formation $formation, Request $request)
    {
        if (Auth()->user()->cannot('edit things')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'description' => 'nullable|string|max:255',
        ]);

        $formation->description     = $request->description;
        $formation->updated_user_id = Auth()->user()->id;

        $formation->save();

        return redirect()->route('formations.index');
    }
}
