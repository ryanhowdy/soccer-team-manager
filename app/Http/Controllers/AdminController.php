<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::with('managedPlayers.player')
            ->get();

        return view('admin.index', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed'],
        ]);

        $user = new User;

        $user->email     = $request->email;
        $user->password  = Hash::make($request->password);

        $user->save();

        return redirect()->route('admin.index');
    }
}
