<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Club;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ClubController extends Controller
{
    /**
     * First - when no managed club/team exists, show them this page
     * to help create the first one.
     *
     * @return Illuminate\View\View
     */
    public function first()
    {
        $clubs = Club::all();

        if ($clubs->isNotEmpty())
        {
            return redirect()->route('teams.first');
        }

        session(['first' => 'club']);

        return view('clubs.first', [
            'createClubAction' => route('clubs.store'),
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
            'name'    => 'required|string|max:255|unique:clubs,name',
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

        if ($request->filled('city'))
        {
            $club->city = $request->city;
        }
        if ($request->filled('state'))
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
        if ($request->filled('website'))
        {
            $club->website = $request->website;
        }
        if ($request->filled('notes'))
        {
            $club->notes = $request->notes;
        }

        $club->save();

        if ($request->session()->exists('first'))
        {
            return redirect()->route('teams.first');
        }

        return redirect()->route('teams.index');
    }

    /**
     * edit 
     * 
     * @param Club $club 
     * @return Illuminate\View\View
     */
    public function edit(Club $club)
    {
        return view('clubs.edit', [
            'club' => $club,
        ]);
    }

    /**
     * update
     * 
     * @param Club    $club 
     * @param Request $request 
     * @return null
     */
    public function update(Club $club, Request $request)
    {
        $validated = $request->validate([
            'name'    => [
                'required',
                'string',
                'max:255',
                Rule::unique('clubs', 'name')->ignore($club->id)
            ],
            'city'    => 'nullable|string|max:255',
            'state'   => 'nullable|string|max:2',
            'logo'    => 'nullable|image',
            'website' => 'nullable|string|max:255',
            'notes'   => 'nullable|string|max:255',
        ]);

        $club->name            = $request->name;
        $club->updated_user_id = Auth()->user()->id;

        if ($request->filled('city'))
        {
            $club->city = $request->city;
        }
        if ($request->filled('state'))
        {
            $club->state = $request->state;
        }

        // we renamed the club, rename the logo to match
        if ($club->getOriginal('name') !== $request->name)
        {
            $origExtension = pathinfo(storage_path($club->logo), PATHINFO_EXTENSION);

            // create new filename based on club name
            $newFilename = strtolower($request->name);
            $newFilename = str_replace(' ', '_', $newFilename);
            $newFilename = preg_replace("/[^0-9a-z_]/", '', $newFilename);
            $newFilename = $newFilename . '.' . $origExtension;

            // rename file
            $orig = preg_replace('/storage\//', '', $club->logo);
            $new  = 'logos/' . $newFilename;

            $worked = Storage::disk('public')->move($orig, $new);

            $club->logo = 'storage/logos/' . $newFilename;
        }

        if ($request->has('logo'))
        {
            // delete the previous logo
            if ($club->logo !== 'img/logo_none.png')
            {
                Storage::delete($club->logo);
            }

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
        if ($request->filled('website'))
        {
            $club->website = $request->website;
        }
        if ($request->filled('notes'))
        {
            $club->notes = $request->notes;
        }

        $club->save();

        return redirect()->route('teams.index');
    }
}
