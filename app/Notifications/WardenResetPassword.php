<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;

class WardenResetPassword extends ResetPassword
{
    protected function resetUrl($notifiable): string
    {
        return route('warden.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
