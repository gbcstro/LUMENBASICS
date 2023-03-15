<?php
    return [

        'defaults' => [
            'guard' => 'api',
            'passwords' => 'users',
        ],
        
        'guards' => [
            'api' => [
                'driver' => 'jwt',
                'provider' => 'users',
            ],
        ],

        'passwords' => [
            'user' => [
                'provider' => 'user',
                'table' => 'password_resets',
                'expire' => 60,
                'throttle' => 60,
            ],
          ],

        'password_timeout' => 10800,
        
        'providers' => [
            'users' => [
                'driver' => 'eloquent',
                'model' => \App\Models\User::class
            ]
        ]
    ];
