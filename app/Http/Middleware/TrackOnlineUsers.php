<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class TrackOnlineUsers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Track online users in a centralized cache key
            $onlineUsersKey = 'online_users';
            $onlineUsers = Cache::get($onlineUsersKey, []);
            $onlineUsers[$user->id] = now()->timestamp;
            Cache::put($onlineUsersKey, $onlineUsers, now()->addMinutes(5));

            // Also set individual user online status for backward compatibility
            Cache::put('online_user_' . $user->id, true, now()->addMinutes(5));
        }

        return $next($request);
    }
}
