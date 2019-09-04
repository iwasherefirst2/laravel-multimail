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
              'pass'     => env('first_mail_password'),
              'username' => env('first_mail_username'),
              'from'     => "Max Musterman",
            ],
        'contact@foo.org'  =>
            [
              'pass'     => env('second_mail_password'),
              'username' => env('second_mail_username'),
              'from'     => "Alice Armania",
            ],
    ],

];
