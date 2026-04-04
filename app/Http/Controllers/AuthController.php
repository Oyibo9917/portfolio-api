<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class AuthController
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), $validator->errors(), 422);
        }

        return $this->authService->register($request->only(['name', 'email', 'password']));
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), $validator->errors(), 422);
        }

        return $this->authService->login($request->email, $request->password);
    }

    public function getAuthUser(Request $request)
    {
        $user = auth()->user();

        if ($user) {
            return ApiResponse::success($user);
        }

        return ApiResponse::error('Unauthorized', null, 401);
    }

    public function logout(Request $request)
    {
        return $this->authService->logout($request->user());
    }

    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), $validator->errors(), 422);
        }

        return $this->authService->sendResetLink($request->email);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), $validator->errors(), 422);
        }

        return $this->authService->resetPassword($request->only(['email', 'password', 'password_confirmation', 'token']));
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), $validator->errors(), 422);
        }

        return $this->authService->changePassword(
            $request->user(),
            $request->old_password,
            $request->password
        );
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        return $this->authService->handleGoogleCallback();
    }
}
