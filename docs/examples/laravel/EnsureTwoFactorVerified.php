<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->hasEnabledTwoFactorAuthentication()) {
            return $next($request);
        }

        if ($request->session()->get('two_factor_verified')) {
            return $next($request);
        }

        return redirect()->route('two-factor.verify');
    }
}
