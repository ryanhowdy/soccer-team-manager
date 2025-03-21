<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManagedPlayer;
use App\Models\Player;

class MeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('settings.index');
    }

}
