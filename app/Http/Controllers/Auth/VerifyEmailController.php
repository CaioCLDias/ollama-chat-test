<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;


class VerifyEmailController extends Controller
{
 
    public function __invoke(Request $request): RedirectResponse
    {
        $user = User::findOrFail($request->route('id'));

        \Log::info('🔒 Verificando assinatura de email', [
            'full_url' => $request->fullUrl(),
            'expected_hash' => sha1($user->getEmailForVerification()),
            'provided_hash' => $request->route('hash'),
            'is_https' => $request->isSecure(),
            'host' => $request->getHost(),
        ]);

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
