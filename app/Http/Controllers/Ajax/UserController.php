<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function store(User $user, Request $request)
    {
        $validated = $request->validate([
            'role' => ['required', 'in:admin,manager'],
        ]);

        $user->assignRole($request->role);

        return response()->json([
            'success' => true,
            'data'    => $user->toArray(),
        ], 200);
    }
}
