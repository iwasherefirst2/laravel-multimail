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
        'office@example.com' => [
            'from_name'     => 'Max Musterman',
            'reply_to_mail' => 'reply@example.com',
        ],
        'contact@example.net'  => [
            'driver'        => 'smtp',
            'from_name'     => 'The Hive',
            'reply_to_mail' => 'contact@example.com',
        ],
    ],
];
