<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Active
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): mixed  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user?->hasActiveAccess()) {
            return redirect()->route('filament.auth.auth.deactivated-access.prompt');
        }

        return $next($request);
    }
}
