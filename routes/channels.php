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
    // Support UUID primary key (`id`) and legacy `user_id` fields.
    $userId = (string) $user->id;
    $legacyUserId = isset($user->user_id) ? (string) $user->user_id : null;

    $idParam = (string) $id;

    $matchesId = $userId === $idParam;
    $matchesLegacy = $legacyUserId !== null && $legacyUserId === $idParam;
    $isAdmin = $user->type === 'admin';

    $authorized = ($isAdmin && ($matchesId || $matchesLegacy));

    if (!$authorized) {
        Log::debug('Admin channel auth failed', [
            'user_id' => $userId,
            'legacy_user_id' => $legacyUserId,
            'channel_id' => $idParam,
            'user_type' => $user->type,
            'matches_id' => $matchesId,
            'matches_legacy' => $matchesLegacy,
            'is_admin' => $isAdmin,
        ]);
    }

    return $authorized;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    // Support UUID primary key (`id`) and legacy `user_id` fields.
    $userId = (string) $user->id;
    $legacyUserId = isset($user->user_id) ? (string) $user->user_id : null;

    $idParam = (string) $id;

    $matchesId = $userId === $idParam;
    $matchesLegacy = $legacyUserId !== null && $legacyUserId === $idParam;
    $isNotAdmin = $user->type !== 'admin';

    $authorized = ($isNotAdmin && ($matchesId || $matchesLegacy));

    if (!$authorized) {
        Log::debug('User channel auth failed', [
            'user_id' => $userId,
            'legacy_user_id' => $legacyUserId,
            'channel_id' => $idParam,
            'user_type' => $user->type,
            'matches_id' => $matchesId,
            'matches_legacy' => $matchesLegacy,
            'is_not_admin' => $isNotAdmin,
        ]);
    }

    return $authorized;
});
