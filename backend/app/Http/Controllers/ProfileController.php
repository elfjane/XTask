<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user.
     */
    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Update the authenticated user profile.
     */
    public function update(Request $request)
    {
        $user = $request->user();
        if (!$user)
            return response()->json(['error' => 'User not found'], 404);

        $user->update($request->only(['name', 'title', 'department', 'photo_url']));
        return response()->json(['message' => 'Profile updated']);
    }
}
