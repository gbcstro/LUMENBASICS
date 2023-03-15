<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Traits\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;

use App\Models\Task;


class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject, CanResetPasswordContract {
    
    use Authenticatable, Authorizable, HasFactory, Notifiable, MustVerifyEmail, CanResetPassword;

    public $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password'

    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'password', 'id', 'created_at', 'updated_at', 'remember_token'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public function getJWTCustomClaims() {
        return [];
    }

    protected static function boot(){
        parent::boot();
        static::saved(function ($model) {
        /**
        * If user email have changed email verification is required
        */
            if( $model->isDirty('email') ) {
                $model->setAttribute('email_verified_at', null);
                $model->sendEmailVerificationNotification();
            }
        });
    }

    public function tasks() {
        return $this->hasMany(Task::class);
    }
}
