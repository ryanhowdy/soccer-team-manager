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
     * @return Illuminate\Support\Facades\View
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
        $scheduled = Result::with('competition')
            ->with('location')
            ->with('homeTeam')
            ->with('awayTeam')
            ->where('status', 'S')
            ->get();
        dump($scheduled);
        return view('home', [
            'scheduled' => $scheduled,
        ]);
    }
}
