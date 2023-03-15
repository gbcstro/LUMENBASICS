<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Tymon\JWTAuth\Facades\JWTAuth;

class ResetPassword extends Notification {

    public $token;

    public static $toMailCallback;


    public function __construct($token){
        $this->token = $token;
    }

    public function via($notifiable){
        return ['mail'];
    }

    public function toMail($notifiable){

        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        return (new MailMessage)
            ->subject(Lang::get('Reset Password Notification'))
            ->line(Lang::get('You are receiving this email because we received a password reset request for your account.'))
            // ->action(Lang::get('Reset Password'), url(route('password.reset', ['token' => $this->token, 'email' => $notifiable->email], false)))
            ->line(Lang::get('This password reset link will expire in count minutes.'))
            ->line(Lang::get('If you did not request a password reset, no further action is required.'));
    }

    public static function toMailUsing($callback){
        static::$toMailCallback = $callback;
    }
}