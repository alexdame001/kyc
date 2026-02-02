<?php

return [

    'default' => env('MAIL_MAILER', 'smtp'),

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'kyc@ibedc.com'),
        'name'    => env('MAIL_FROM_NAME', 'IBEDC KYC System'),
    ],

    'mailers' => [
        'smtp' => [
            'transport'  => 'smtp',
            'host'       => env('MAIL_HOST', 'smtp.office365.com'),
            'port'       => env('MAIL_PORT', 587),
            'encryption' => null,  // ← Critical for Office365 on Windows
            'username'   => env('MAIL_USERNAME'),
            'password'   => env('MAIL_PASSWORD'),
            'timeout'    => null,
            'verify_peer' => false,  // ← Direct disable (Symfony Mailer needs this)
            'stream' => [
                'ssl' => [
                    'allow_self_signed' => true,
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                ],
            ],
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path'      => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel'   => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers'   => [
                'smtp',
                'log',
            ],
        ],
    ],

];