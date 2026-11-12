<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Sms\SmsSender;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    private const OTP_TTL_MINUTES = 10;
    private const OTP_MAX_ATTEMPTS = 5;
    private const OTP_RESEND_SECONDS = 60;
    private const RESET_TOKEN_TTL_MINUTES = 15;

    public function register(RegisterRequest $request): JsonResponse
    {
        if (User::whereIn('phone', PhoneNumber::variants($request->phone))->exists()) {
            return $this->fail('This mobile number is already registered.', 422, ['phone' => ['This mobile number is already registered.']]);
        }

        $user = User::create([
            'name'          => $request->name,
            'phone'         => $request->phone,
            'password'      => Hash::make($request->password),
            'referral_code' => $request->referral_code,
            'type'          => 2,
            'status'        => true,
            'is_verified'   => false,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Registration successful.',
            'data'    => [
                'token' => $user->createToken('auth_token')->plainTextToken,
                'user'  => new UserResource($user),
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::whereIn('phone', PhoneNumber::variants($request->phone))->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->fail('Invalid phone number or password.', 401);
        }

        if (! $user->status) {
            return $this->fail('Your account has been deactivated. Please contact support.', 403);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Login successful.',
            'data'    => [
                'token' => $user->createToken('auth_token')->plainTextToken,
                'user'  => new UserResource($user),
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data'   => new UserResource($request->user()->load('location')),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name'        => ['sometimes', 'required', 'string', 'min:2', 'max:100'],
            'email'       => ['sometimes', 'nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'bio'         => ['sometimes', 'nullable', 'string', 'max:500'],
            'address'     => ['sometimes', 'nullable', 'string', 'max:255'],
            'location_id' => ['sometimes', 'nullable', Rule::exists('locations', 'id')->where('status', true)],
            'image'       => ['sometimes', 'nullable', 'string', 'max:255', 'regex:#^avatars/[\w/.-]+$#'],
        ]);

        if (! empty($data['image']) && ! Storage::disk('public')->exists($data['image'])) {
            return $this->fail('The profile photo was not found. Please upload it again.', 422, ['image' => ['The profile photo was not found.']]);
        }

        $user->update($data);

        return response()->json([
            'status'  => true,
            'message' => 'Profile updated.',
            'data'    => new UserResource($user->fresh('location')),
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:6', 'max:100', 'confirmed', 'different:current_password'],
        ]);

        $user = $request->user();
        if (! Hash::check($request->current_password, $user->password)) {
            return $this->fail('Your current password is incorrect.', 422, ['current_password' => ['Your current password is incorrect.']]);
        }

        $user->update(['password' => Hash::make($request->password)]);
        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return response()->json(['status' => true, 'message' => 'Password changed.']);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['status' => true, 'message' => 'Logged out successfully.']);
    }

    public function forgotPassword(Request $request, SmsSender $sms): JsonResponse
    {
        $request->validate(['phone' => ['required', 'string', 'max:20']]);
        $generic = 'If this number is registered, we have sent a 6-digit code by SMS. It expires in ' . self::OTP_TTL_MINUTES . ' minutes.';

        $user = User::whereIn('phone', PhoneNumber::variants($request->phone))->where('status', true)->first();
        if (! $user) {
            return response()->json(['status' => true, 'message' => $generic]);
        }

        if (! Cache::add($this->otpCooldownKey($user->id), 1, self::OTP_RESEND_SECONDS)) {
            return $this->fail('Please wait a minute before requesting another code.', 429);
        }

        $code = (string) random_int(100000, 999999);
        Cache::put($this->otpKey($user->id), ['hash' => Hash::make($code), 'attempts' => 0], now()->addMinutes(self::OTP_TTL_MINUTES));

        $sms->send($user->phone, "Your PetMart password reset code is {$code}. It expires in " . self::OTP_TTL_MINUTES . ' minutes. Do not share it.');

        return response()->json(['status' => true, 'message' => $generic]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'otp'   => ['required', 'digits:6'],
        ]);

        $invalid = fn () => $this->fail('The code is incorrect or has expired.', 422, ['otp' => ['The code is incorrect or has expired.']]);

        $user = User::whereIn('phone', PhoneNumber::variants($request->phone))->where('status', true)->first();
        $entry = $user ? Cache::get($this->otpKey($user->id)) : null;
        if (! $user || ! $entry) {
            return $invalid();
        }

        if (! Hash::check($request->otp, $entry['hash'])) {
            $entry['attempts']++;
            if ($entry['attempts'] >= self::OTP_MAX_ATTEMPTS) {
                Cache::forget($this->otpKey($user->id));
            } else {
                Cache::put($this->otpKey($user->id), $entry, now()->addMinutes(self::OTP_TTL_MINUTES));
            }

            return $invalid();
        }

        Cache::forget($this->otpKey($user->id));
        $token = Str::random(64);
        Cache::put($this->resetTokenKey($token), $user->id, now()->addMinutes(self::RESET_TOKEN_TTL_MINUTES));

        return response()->json([
            'status'  => true,
            'message' => 'Code verified. Choose a new password.',
            'data'    => ['token' => $token, 'expires_in' => self::RESET_TOKEN_TTL_MINUTES * 60],
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => ['required', 'string', 'size:64'],
            'password' => ['required', 'string', 'min:6', 'max:100'],
        ]);

        $userId = Cache::pull($this->resetTokenKey($request->token));
        $user = $userId ? User::find($userId) : null;
        if (! $user) {
            return $this->fail('This reset link has expired. Please start again.', 422, ['token' => ['This reset link has expired.']]);
        }

        $user->update(['password' => Hash::make($request->password)]);
        $user->tokens()->delete();

        return response()->json(['status' => true, 'message' => 'Password updated. You can now log in.']);
    }

    private function otpKey(int $userId): string
    {
        return "password_reset:otp:{$userId}";
    }

    private function otpCooldownKey(int $userId): string
    {
        return "password_reset:cooldown:{$userId}";
    }

    private function resetTokenKey(string $token): string
    {
        return 'password_reset:token:' . hash('sha256', $token);
    }

    private function fail(string $message, int $status, array $errors = []): JsonResponse
    {
        return response()->json(array_filter([
            'status'  => false,
            'message' => $message,
            'errors'  => $errors ?: null,
        ], fn ($v) => $v !== null), $status);
    }
}
