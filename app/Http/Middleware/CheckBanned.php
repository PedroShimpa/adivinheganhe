<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckBanned
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->banned) {
            Log::info("Usuário banido tentou acessar: {$user->id} ({$user->username})");

            Auth::logout();

            return redirect()->route('banned.view');
        }

        return $next($request);
    }
}
