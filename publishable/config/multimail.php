<?php

return [
    /*
    |--------------------------------------------------------------------------
    | List your email providers
    |--------------------------------------------------------------------------
    |
    | Enjoy a life with multimail
    |
    */

    'emails'  => [
        'office@example.com' =>
            [
              'pass'          => env('MAIL_PASSWORD'),
              'username'      => env('MAIL_USERNAME'),
              'from_name'     => "Max Musterman",
              'reply_to_mail' => 'reply@example.com',
            ],
        'contact@example.net'  =>
            [
              'pass'     => env('second_mail_password'),
            ],
    ],

    'provider' => [
      'default' =>
        [
          'host'      => env('MAIL_HOST'),
          'port'      => env('MAIL_PORT'),
          'encryption' => env('MAIL_ENCRYPTION'),
        ]
    ],


];
