<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Display the register view
     *
     * @return Illuminate\View\View
     */
    public function create()
    {
        $firstUser = User::first();

        if (!is_null($firstUser))
        {
            return redirect()->route('index');
        }

        return view('auth.register');
    }

    /**
     * Handle the login request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $firstUser = User::first();

        if (!is_null($firstUser))
        {
            return redirect()->route('index');
        }

        $credentials = $request->validate([
            'email'    => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed'],
        ]);

        $user = new User;

        $user->email     = $request->email;
        $user->password  = Hash::make($request->password);

        $user->save();

        return redirect()->route('index');
    }
}
