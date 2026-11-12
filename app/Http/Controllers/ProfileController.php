<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function getProfile(Request $request): JsonResponse
    {
        return response()->json(['status' => 2000, 'result' => $this->present($request->user())]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name'             => ['required', 'string', 'min:2', 'max:100'],
            'email'            => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'password'         => ['nullable', 'string', 'min:8', 'max:100', 'confirmed'],
            'current_password' => ['required_with:password', 'nullable', 'string'],
        ]);

        if (! empty($data['password'])) {
            if (! Hash::check((string) $data['current_password'], $user->password)) {
                return response()->json([
                    'status'  => 3000,
                    'message' => 'Your current password is incorrect.',
                    'result'  => ['current_password' => ['Your current password is incorrect.']],
                ], 422);
            }
            $user->password = Hash::make($data['password']);
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->save();

        if (! empty($data['password'])) {
            $user->tokens()->delete();
            auth()->logoutOtherDevices($data['password']);
        }

        return response()->json([
            'status'  => 2000,
            'message' => 'Profile updated.',
            'result'  => $this->present($user),
        ]);
    }

    private function present($user): array
    {
        return [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ];
    }
}
