<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecoverUserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auth/recover-account",
     *     tags={"Auth"},
     *     summary="Recover a soft-deleted user account",
     *     description="Restores a previously deleted user account by email.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="deleted.user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account recovered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Account recovered successfully."),
     *             @OA\Property(property="data", type="string", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Account is already active"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */

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
