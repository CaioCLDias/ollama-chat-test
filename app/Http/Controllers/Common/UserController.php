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
  
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);

            return ApiResponse::success(new UserResource($user), 'User created successfully.', 201);
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to create user.', 500, $e->getMessage());
        }
    }

    public function show(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            return ApiResponse::success(new UserResource($user), 'User details.');
        } catch (\Throwable $e) {
            return ApiResponse::error('User not found.', 404, $e->getMessage());
        }
    }

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


    public function destroy(Request $request): JsonResponse
    {
        try {

            $user = $request->user();
            $user->scheduled_for_deletion_at = now();
            $user->save();
            return ApiResponse::success(null, 'Account deletion scheduled');
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to delete user.', 500, $e);
        }
    }

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
