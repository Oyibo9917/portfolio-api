<?php

namespace App\Services;

use App\Events\UserRegistered;
use App\Helpers\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class AuthService
{
    public function register(array $data): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            event(new UserRegistered($user));

            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return ApiResponse::success([
                'token' => $token,
                'data' => $user
            ], 'User registered successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }

    public function login(string $email, string $password): JsonResponse
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return ApiResponse::error('Invalid credentials', null, 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'data' => $user
        ], 'Login successful');
    }

    public function logout(User $user): JsonResponse
    {
        $user->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Logged out successfully');
    }

    public function sendResetLink(string $email): JsonResponse
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status === Password::RESET_LINK_SENT) {
            return ApiResponse::success(null, 'Password reset link sent to your email');
        }

        return ApiResponse::error('Unable to send reset link', null, 400);
    }

    public function resetPassword(array $data): JsonResponse
    {
        $status = Password::reset(
            $data,
            function ($user) use ($data) {
                $user->forceFill([
                    'password' => Hash::make($data['password']),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? ApiResponse::success(null, 'Password reset successful')
            : ApiResponse::error('Password reset failed', null, 400);
    }

    public function changePassword(User $user, string $oldPassword, string $newPassword): JsonResponse
    {
        if (!Hash::check($oldPassword, $user->password)) {
            return ApiResponse::error('Old password is incorrect', null, 400);
        }

        DB::beginTransaction();
        try {
            $user->update([
                'password' => Hash::make($newPassword)
            ]);

            DB::commit();

            return ApiResponse::success(null, 'Password changed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }

    public function handleGoogleCallback(): JsonResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            DB::beginTransaction();

            $user = User::updateOrCreate(
                ['google_id' => $googleUser->getId()],
                [
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'avatar' => $googleUser->getAvatar(),
                ]
            );

            $token = $user->createToken('auth-token')->plainTextToken;

            DB::commit();

            $frontendUrl = env('VUE_APP_FRONTEND_URL') . '/auth/callback?token=' . $token;

            return ApiResponse::success(['redirect_url' => $frontendUrl], 'Google authentication successful');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }
}
