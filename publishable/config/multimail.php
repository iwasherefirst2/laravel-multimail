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
        'office' =>
            [
              'pass'          => env('MAIL_PASSWORD'),
              'username'      => env('MAIL_USERNAME'),
              'from_mail'     => 'office@crazy.com',
              'from_name'     => "Max Musterman",
              'reply_to_mail' => 'reply@example.com',
            ],
        'contact'  =>
            [
              'pass'     => env('second_mail_password'),
              'username' => env('second_mail_username'),
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
