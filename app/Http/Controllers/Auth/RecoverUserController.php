<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecoverUserController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::withTrashed()->where('email', $request->input('email'))->first();

        if (!$user) {
            return ApiResponse::error('User not found.', 404);
        }

        if (!$user->trashed()) {
            return ApiResponse::error('Account is already active.', 400);
        }

        $user->restore();
        $user->scheduled_for_deletion_at = null;
        $user->save();

        return ApiResponse::success(null, 'Account recovered successfully.');
    }
}
