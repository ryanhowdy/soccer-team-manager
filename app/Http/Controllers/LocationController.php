<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;

class LocationController extends Controller
{
    /**
     * index
     *
     * @return Illuminate\View\View
     */
    public function index(Request $request)
    {
        $locations = Location::all();

        return view('locations.index', [
            'locations' => $locations,
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
            'name'    => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        $location = new Location;

        $location->name            = $request->name;
        $location->address         = $request->address;
        $location->created_user_id = Auth()->user()->id;
        $location->updated_user_id = Auth()->user()->id;

        $location->save();

        if ($request->wantsJson())
        {
            return response()->json([
                'success' => true,
                'data'    => $location->toArray(),
            ], 200);
        }

        return redirect()->route('locations.index');
    }
}
