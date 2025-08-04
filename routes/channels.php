<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('presence', function ($user) {
    if ($user) {
        return [
            'id'   => 'user-' . $user->id,
            'name' => $user->name,
        ];
    }

    return [
        'id'   => 'guest-' . uniqid(),
        'name' => 'Convidado',
    ];
});