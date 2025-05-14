<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Js;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {

        try {
            $credentials = $request->only('email', 'password');

            $user = User::where('email', $credentials['email'])->first();

            if (!$user) {
                return ApiResponse::error('User not found.', 404);
            }

            if ($user->deleted_at) {
                return ApiResponse::error('This user account is scheduled for deletion.', 403);
            }

            if (!Hash::check($credentials['password'], $user->password)) {
                return ApiResponse::error('Invalid credentials.', 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return ApiResponse::success([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ], 'Login successful.');
        } catch (\Exception $e) {
            return ApiResponse::error('Error on login', 500);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return ApiResponse::success(null, 'Logout successful.');
    }
}
