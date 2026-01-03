<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureVip
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !method_exists($user, 'isVip') || !$user->isVip()) {
            return redirect()->route('membership.index')->with('error', 'Este jogo é exclusivo para membros VIP.');
        }

        return $next($request);
    }
}
