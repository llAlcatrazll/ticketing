<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Verify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): ?Response
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user?->hasVerifiedEmail()) {
            return redirect()->route('filament.auth.auth.email-verification.prompt');
        }

        return $next($request);
    }
}
