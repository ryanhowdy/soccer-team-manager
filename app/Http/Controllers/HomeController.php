<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Result;

class HomeController extends Controller
{
    /**
     * Redirects to login or home page
     *
     * @return Illuminate\View\View
     */
    public function index()
    {
        $firstUser = User::first();

        if (is_null($firstUser))
        {
            return redirect()->route('register');
        }
        else if (!Auth()->user())
        {
            return redirect()->route('login');
        }

        return redirect()->route('home');
    }

    /**
     * Display the home view
     *
     * @return Illuminate\View\View
     */
    public function home()
    {
        $todayStart = \Carbon\Carbon::now()->startOfDay();
        $todayEnd   = \Carbon\Carbon::now()->endOfDay();

        $scheduled = Result::with('competition')
            ->with('location')
            ->with('homeTeam.club')
            ->with('awayTeam.club')
            ->where('status', 'S')
            ->whereBetween('date', [$todayStart, $todayEnd])
            ->get();

        return view('home', [
            'scheduled' => $scheduled,
        ]);
    }
}
