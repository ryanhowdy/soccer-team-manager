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
        if (auth()->check())
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
        // We only allow 1 user to register
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

        $user->assignRole('admin');

        return redirect()->route('index');
    }
}
