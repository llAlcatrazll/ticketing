<?php

namespace App\Filament\Panels\Auth\Controllers;

use App\Http\Middleware\Authenticate;
use Filament\Http\Controllers\Auth\EmailVerificationController as Controller;
use Illuminate\Routing\Controllers\HasMiddleware;

class EmailVerificationController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            Authenticate::class,
        ];
    }
}
