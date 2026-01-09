<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return User::with(['department', 'projects'])->get();
    }

    public function show(User $user)
    {
        return $user->load(['department', 'projects']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'employee_id' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'role' => 'required|string',
            'projects' => 'required|array',
            'projects.*' => 'exists:projects,id',
            'photo_url' => 'nullable|string'
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        if (isset($validated['projects'])) {
            $user->projects()->sync($validated['projects']);
        }

        return response()->json($user->load(['department', 'projects']), 201);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'employee_id' => 'nullable|string',
            'department_id' => 'sometimes|exists:departments,id',
            'role' => 'sometimes|string',
            'projects' => 'sometimes|array',
            'projects.*' => 'exists:projects,id',
            'photo_url' => 'nullable|string',
            'password' => 'sometimes|string|min:8'
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        if (isset($validated['projects'])) {
            $user->projects()->sync($validated['projects']);
        }

        return response()->json($user->load(['department', 'projects']));
    }

    public function destroy(User $user)
    {
        $user->update(['is_active' => false]);
        return response()->json(['message' => 'User deactivated']);
    }
}
