<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Club;

class ClubController extends Controller
{
    /**
     * store 
     * 
     * @param Request $request 
     * @return null
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255|unique:clubs',
            'city'    => 'nullable|string|max:255',
            'state'   => 'nullable|string|max:2',
            'logo'    => 'nullable|image',
            'website' => 'nullable|string|max:255',
            'notes'   => 'nullable|string|max:255',
        ]);

        $club = new Club;

        $club->name            = $request->name;
        $club->created_user_id = Auth()->user()->id;
        $club->updated_user_id = Auth()->user()->id;

        if ($request->has('city'))
        {
            $club->city = $request->city;
        }
        if ($request->has('state'))
        {
            $club->state = $request->state;
        }
        if ($request->has('logo'))
        {
            $file = $request->file('logo');

            // rename logo to match club name
            $filename = strtolower($request->name);
            $filename = str_replace(' ', '_', $filename);
            $filename = preg_replace("/[^0-9a-z_]/", '', $filename);
            $filename = $filename . '.' . $file->extension();

            // upload the logo to the filesystem
            $path = $file->storeAs('logos', $filename, 'public');

            // set the logo url in the db
            $club->logo = 'storage/logos/' . $filename;
        }
        if ($request->has('website'))
        {
            $club->website = $request->website;
        }
        if ($request->has('notes'))
        {
            $club->notes = $request->notes;
        }

        $club->save();

        return redirect()->route('teams.index');
    }
}
