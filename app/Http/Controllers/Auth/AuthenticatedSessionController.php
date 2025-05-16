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
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Auth"},
     *     summary="Authenticate and login a user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="access_token", type="string", example="token"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="user@example.com")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials."),
     *     @OA\Response(response=403, description="This user account is scheduled for deletion."),
     *     @OA\Response(response=404, description="User not found."),
     *     @OA\Response(response=500, description="Error on login")
     * )
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
     * @OA\Post(
     *     path="/api/auth/logout",
     *     tags={"Auth"},
     *     summary="Logout the currently authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logout successful.")
     *         )
     *     )
     * )
     */

    public function destroy(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return ApiResponse::success(null, 'Logout successful.');
    }
}
