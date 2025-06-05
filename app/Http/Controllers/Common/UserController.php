<?php

namespace App\Http\Controllers\Common;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/user",
     *     tags={"User"},
     *     summary="Register a new user",
     *     security={},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password"),
     *             @OA\Property(property="password_confirmation", type="string", example="password")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User created successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=419, description="CSRF Token missing or expired")
     * )
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::create($data);

        return ApiResponse::success(new UserResource($user), 'User created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     tags={"User"},
     *     summary="Get authenticated user details",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User details",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            return ApiResponse::success(new UserResource($user), 'User details.');
        } catch (\Throwable $e) {
            return ApiResponse::error('User not found.', 404, $e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/user",
     *     tags={"User"},
     *     summary="Update authenticated user information",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Name"),
     *             @OA\Property(property="email", type="string", example="newemail@example.com"),
     *             @OA\Property(property="password", type="string", example="newpassword"),
     *             @OA\Property(property="password_confirmation", type="string", example="newpassword")
     *         )
     *     ),
     *     @OA\Response(response=200, description="User updated successfully"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(UpdateUserRequest $request): JsonResponse
    {
        try {

            $data = $request->validated();
            $user = $request->user();

            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user->fill($data)->save();

            return ApiResponse::success(new UserResource($user), 'User updated successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to update user.', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/user/delete",
     *     tags={"User"},
     *     summary="Schedule account deletion for authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Account deletion scheduled"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(Request $request): JsonResponse
    {
        try {

            $user = $request->user();

            if (!$user) {
                return ApiResponse::error('User not found', 404);
            }

            $user->scheduled_for_deletion_at = now()->addDays(7);
            $user->save();

            return ApiResponse::success(null, 'Account deletion scheduled');
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to delete user.', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/user/cancel-deletion",
     *     tags={"User"},
     *     summary="Cancel scheduled account deletion",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Account deletion cancelled"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function cancelDeletion(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->scheduled_for_deletion_at = null;
            $user->save();

            return ApiResponse::success(new UserResource($user), 'Account deletion cancelled.');
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to cancel user deletion.', 500, $e->getMessage());
        }
    }
}
