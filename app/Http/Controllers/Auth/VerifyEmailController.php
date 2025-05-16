<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;


class VerifyEmailController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/auth/verify-email/{id}/{hash}",
     *     tags={"Auth"},
     *     summary="Verify user's email address",
     *     description="Endpoint to mark the user's email as verified using a signed URL.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         required=true,
     *         description="SHA1 hash of the user's email",
     *         @OA\Schema(type="string", example="f4a6e6c4a4a90a739c51bc6a4f55f90e9f8f83d1")
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to email verified success page"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Invalid hash or verification error"
     *     ),
     *     security={}
     * )
     */

    public function __invoke(Request $request): RedirectResponse
    {
        $user = User::findOrFail($request->route('id'));

        if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid hash.');
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect(config('app.frontend_url') . '/email-verified-success');
    }
}
