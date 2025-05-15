<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UserControler extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $users = User::all();
            return ApiResponse::success(UserResource::collection($users), 'Users list');
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to list users.', 500, $e);
        }
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);

            return ApiResponse::success(new UserResource($user), 'User created successfully.', 201);
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to create user.', 500, $e);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            return ApiResponse::success(new UserResource($user), 'User details.');
        } catch (\Throwable $e) {
            return ApiResponse::error('User not found.', 404, $e);
        }
    }

    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $data = $request->validated();

            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user->fill($data)->save();

            return ApiResponse::success(new UserResource($user), 'User updated successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to update user.', 500, $e);
        }
    }



    public function destroy(Request $request, $id): JsonResponse
    {
        try{
            $request->validate([
            'deletion_date' => 'required|date',
        ]);
        $user = User::findOrFail($id);

        $user->scheduled_for_deletion_at = $request->input('deletion_date');
        $user->save();

        return ApiResponse::success($user, 'User scheduled for deletion.');
        }catch (\Throwable $e) {
            return ApiResponse::error('Failed to schedule user for deletion.', 500, $e->getMessage());
        }
        
    }
}
