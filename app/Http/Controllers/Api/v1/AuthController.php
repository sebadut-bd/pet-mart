<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register customer/seller with phone number and password.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'          => $request->name,
            'phone'         => $request->phone,
            'password'      => Hash::make($request->password),
            'referral_code' => $request->referral_code,
            'type'          => 2, // Default: Seller/Buyer hybrid customer account
            'status'        => true,
            'is_verified'   => false,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Registration successful.',
            'data'    => [
                'token' => $token,
                'user'  => new UserResource($user),
            ],
        ], 201);
    }

    /**
     * Login customer/seller with phone number and password.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid phone number or password.',
            ], 401);
        }

        if (!$user->status) {
            return response()->json([
                'status'  => false,
                'message' => 'Your account has been deactivated. Please contact support.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Login successful.',
            'data'    => [
                'token' => $token,
                'user'  => new UserResource($user),
            ],
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data'   => new UserResource($request->user()->load('location')),
        ]);
    }

    /**
     * Logout authenticated user.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Phone-based password reset placeholder (ready for OTP integration).
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|string']);

        $phone = preg_replace('/[^\d+]/', '', $request->phone);
        if (!str_starts_with($phone, '+880')) {
            $phone = '+880' . ltrim($phone, '0');
        }

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'No account found with this phone number.',
            ], 44);
        }

        // Demo OTP code (in production sent via SMS gateway)
        $otp = '123456';
        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Password reset OTP sent to phone number. (Demo OTP: 123456)',
        ]);
    }
}
