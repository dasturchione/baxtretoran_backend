<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.notifications', function ($user) {
    // faqat admin role’ga ega foydalanuvchilar
    return $user->hasAnyRole(['admind', 'super-admin']); ;
});

Broadcast::channel('user.notifications.{id}', function ($user) {
    return (int) $user->id === (int) $id;
});