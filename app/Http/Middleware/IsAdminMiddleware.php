<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IsAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->isAdmin()) {
            Log::info("Usuário {$user->id} ({$user->username}) acessou a rota: " . $request->path());
            return $next($request);
        }

        Log::warning("Acesso negado para usuário " . ($user ? "{$user->id} ({$user->username})" : "guest") . " na rota: " . $request->path());

        return redirect()->route('home');
    }
}
