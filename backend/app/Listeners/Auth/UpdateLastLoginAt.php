<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserLoggedIn;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateLastLoginAt implements ShouldQueue
{
    public function handle(UserLoggedIn $event): void
    {
        $event->user->forceFill([
            'last_login_at' => now(),
        ])->save();
    }
}
