<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('admin.{id}', function ($user, $id) {
    $authorized = (int) $user->id === (int) $id && $user->type === 'admin';

    if (!$authorized) {
        Log::debug('Admin channel auth failed', [
            'user_id' => $user->id,
            'channel_id' => $id,
            'user_type' => $user->type,
            'id_match' => (int) $user->id === (int) $id,
            'type_match' => $user->type === 'admin',
        ]);
    }

    return $authorized;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    $authorized = (int) $user->id === (int) $id && $user->type !== 'admin';

    if (!$authorized) {
        Log::debug('User channel auth failed', [
            'user_id' => $user->id,
            'channel_id' => $id,
            'user_type' => $user->type,
            'id_match' => (int) $user->id === (int) $id,
            'type_match' => $user->type !== 'admin',
        ]);
    }

    return $authorized;
});
