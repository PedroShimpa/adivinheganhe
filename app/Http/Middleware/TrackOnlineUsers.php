<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Events\OnlineUsersUpdated;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

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

            // Clean up expired users (inactive for more than 5 minutes)
            $onlineUsers = array_filter($onlineUsers, function ($timestamp) {
                return now()->timestamp - $timestamp < 300; // 5 minutes
            });

            $onlineUsers[$user->id] = now()->timestamp;
            Cache::put($onlineUsersKey, $onlineUsers, now()->addMinutes(5));

            // Also set individual user online status for backward compatibility
            Cache::put('online_user_' . $user->id, true, now()->addMinutes(5));

            // Broadcast updated online users count and list every 30 seconds to avoid spam
            $lastBroadcastKey = 'last_online_broadcast';
            $lastBroadcast = Cache::get($lastBroadcastKey, 0);

            if (now()->timestamp - $lastBroadcast > 30) { // Broadcast every 30 seconds
                $onlineUserIds = array_keys($onlineUsers);
                $users = User::whereIn('id', $onlineUserIds)->where('banned', false)->select('id', 'name')->get()->toArray();

                broadcast(new OnlineUsersUpdated(count($onlineUsers), $users));
                Cache::put($lastBroadcastKey, now()->timestamp, 30);
            }
        }

        return $next($request);
    }
}
