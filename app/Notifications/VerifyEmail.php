<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Tymon\JWTAuth\Facades\JWTAuth;

class VerifyEmail extends Notification {

    use Queueable;

    public function __construct(){
        //
    }

    public static $toMailCallback;
   
    public function via($notifiable) {
        return ['mail'];
    }

    public function toMail($notifiable) {
       $verficationURL = $this->verificationUrl($notifiable);
        
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $verficationURL);
        }
        
        return (new MailMessage)
            ->subject(Lang::get('Verify Email Address'))
            ->line(Lang::get('Please click the button below to verify your email address.'))
            ->action(Lang::get('Verify Email Address'), $verficationURL)
            ->line(Lang::get('If you did not create an account, no further action is required.'));
    }

    protected function verificationUrl($notifiable) {
        $token = JWTAuth::fromUser($notifiable);
        return route('email.redirect', ['token' => $token], false);
    }

    public static function toMailUsing($callback)
    {
        static::$toMailCallback = $callback;
    }
}
