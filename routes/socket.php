<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Pusher\Pusher;

Route::post('/broadcasting/auth-mixed', function (Request $request) {
    $pusher = new Pusher(
        env('REVERB_APP_KEY'),
        env('REVERB_APP_SECRET'),
        env('REVERB_APP_ID'),
        ['cluster' => env('PUSHER_APP_CLUSTER')]
    );

    $user = auth()->user();
    if ($user) {
        $id   = 'user-' . $user->id;
        $info = ['name' => $user->name];
    } else {
        $id   = 'guest-' . substr(md5($request->ip() . microtime()), 0, 8);
        $info = ['name' => 'Visitante'];
    }

    return $pusher->presence_auth(
        $request->channel_name,
        $request->socket_id,
        $id,
        $info
    );
});
