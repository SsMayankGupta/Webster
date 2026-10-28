<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return $user->isStudent() && (int) $user->id === (int) $id;
}, ['guards' => ['student']]);

Broadcast::channel('guest-queue', fn ($user): bool => $user->role === 'contractor', ['guards' => ['contractor']]);
