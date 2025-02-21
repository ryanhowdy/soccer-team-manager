<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Display the login view
     *
     * @return Illuminate\View\View
     */
    public function create()
    {
        $firstUser = User::first();

        if (is_null($firstUser))
        {
            return redirect()->route('register');
        }

        return view('auth.login');
    }

    /**
     * Handle the login request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->has('remember-me') ? true : false;

        // Check credentials
        if (Auth::attempt($credentials, $remember))
        {
            $request->session()->regenerate();

            return redirect()->intended('home');
        }

        return back()->withError([
            'email' => 'Incorrect login',
        ]);
    }

    /**
     * destroy
     *
     * @param Request $request
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard()->logout();
        $request->session()->flush();
    
        return redirect()->route('index');
    }
}
