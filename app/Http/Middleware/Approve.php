<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Approve
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

        if (! $user?->hasApprovedAccount()) {
            return redirect()->route('filament.auth.auth.account-approval.prompt');
        }

        return $next($request);
    }
}
