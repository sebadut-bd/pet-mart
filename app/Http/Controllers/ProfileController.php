<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function getProfile(Request $request): JsonResponse
    {
        $user = $request->user() ?? \App\Models\User::first();

        $userData = [
            'id'        => $user->id,
            'name'      => $user->name,
            'username'  => $user->name,
            'email'     => $user->email,
            'image'     => $user->avatar ?? null,
            'layout'    => 'vertical',
        ];

        return response()->json([
            'status' => 2000,
            'data'   => [$userData],
            'result' => $userData,
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user() ?? \App\Models\User::first();

        $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $data = [
            'email' => $request->email,
        ];

        if ($request->filled('username') || $request->filled('name')) {
            $data['name'] = $request->username ?? $request->name;
        }

        if ($request->filled('password')) {
            if ($request->filled('old_password') && !Hash::check($request->old_password, $user->password)) {
                return response()->json([
                    'status'  => 400,
                    'message' => 'Old password does not match.',
                ], 400);
            }
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'status'  => 2000,
            'message' => 'Profile updated successfully.',
            'result'  => $user,
        ]);
    }
}
