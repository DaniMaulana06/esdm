<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOperatorEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Operator BKU yang belum verifikasi harus ke halaman OTP.
        if (
            $user->role === 'operator_bku' &&
            !$user->hasVerifiedEmail()
        ) {
            return redirect()->route('email-otp.show');
        }

        return $next($request);
    }
}
