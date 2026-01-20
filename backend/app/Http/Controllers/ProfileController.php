<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user with relations.
     */
    public function show(Request $request)
    {
        return response()->json($request->user()->load(['department']));
    }

    /**
     * Update the authenticated user profile.
     */
    public function update(ProfileUpdateRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($request->has('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['The provided password does not match your current password.'],
                ]);
            }
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return response()->json($user->load(['department']));
    }

    /**
     * Upload and update user avatar.
     */
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
                Storage::delete($oldPath);
            }

            $path = $request->file('avatar')->store('public/avatars');
            $url = Storage::url($path);

            $user->update(['photo_url' => $url]);

            return response()->json([
                'message' => 'Avatar updated successfully',
                'photo_url' => $url
            ]);
        }

        return response()->json(['message' => 'No file uploaded'], 400);
    }
}
