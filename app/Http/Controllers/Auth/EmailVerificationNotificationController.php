<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class EmailVerificationNotificationController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auth/email/verification-notification",
     *     tags={"Auth"},
     *     summary="Resend the email verification link",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Email already verified or verification link sent.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Verification link sent."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="verification_url",
     *                     type="string",
     *                     format="uri",
     *                     example="http://localhost/api/auth/verify-email/1/6e0a07e13b187..."
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return ApiResponse::success(null, 'Email already verified.');
        }

        // Generate a provisory signed URL for email verification
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $request->user()->id,
                'hash' => sha1($request->user()->email),
            ]
        );

        $request->user()->sendEmailVerificationNotification();

        return ApiResponse::success(['verification_url' => $verificationUrl,], 'Verification link sent');
    }
}
