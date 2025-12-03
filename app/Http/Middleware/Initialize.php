<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Initialize
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User $user */
        $user = $request->user();

        if (in_array($user->role, [UserRole::ADMIN, UserRole::MODERATOR, UserRole::AGENT, UserRole::USER]) && $user->organization()->doesntExist()) {
            return redirect()->route('filament.auth.auth.organization-initialization.prompt');
        }

        return $next($request);
    }
}
