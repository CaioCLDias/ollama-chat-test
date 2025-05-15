<?php

namespace App\Http\Controllers\Common;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisterUserController extends Controller
{
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
}
