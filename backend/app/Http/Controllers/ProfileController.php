<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'current_password' => 'required_with:password',
            'password' => 'sometimes|min:8|confirmed',
        ]);

        if ($request->has('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['The provided password does not match your current password.'],
                ]);
            }
            $user->password = Hash::make($request->password);
        }

        $user->fill($request->only(['name', 'email', 'title', 'department', 'photo_url']));
        $user->save();

        return response()->json($user);
    }
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();

        if ($request->file('avatar')) {
            // Delete old avatar if exists and is local
            if ($user->photo_url && str_contains($user->photo_url, '/storage/avatars/')) {
                $oldPath = str_replace('/storage/', 'public/', parse_url($user->photo_url, PHP_URL_PATH));
                \Illuminate\Support\Facades\Storage::delete($oldPath);
            }

            $path = $request->file('avatar')->store('public/avatars');
            $url = \Illuminate\Support\Facades\Storage::url($path);

            $user->photo_url = $url;
            $user->save();

            return response()->json([
                'message' => 'Avatar updated successfully',
                'photo_url' => $url
            ]);
        }

        return response()->json(['message' => 'No file uploaded'], 400);
    }
}
