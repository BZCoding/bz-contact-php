<?php
return [
    'settings' => [
        'displayErrorDetails' => ($_SERVER['SLIM_MODE'] !== 'production') ? true : false, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/views/default/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'bz-contact',
            'path' => ($_SERVER['LOG_PATH']) ? $_SERVER['LOG_PATH'] : 'php://stdout',
        ],

        'database' => [
            'host' => $_SERVER['DATABASE_HOST'], // i.e mongodb://1.2.3.4:27017
            'name' => $_SERVER['DATABASE_NAME'],
            'collection' => $_SERVER['DATABASE_COLLECTION'],
            'username' => ($_SERVER['DATABASE_USERNAME']) ? $_SERVER['DATABASE_USERNAME'] : null,
            'password' => ($_SERVER['DATABASE_PASSWORD']) ? $_SERVER['DATABASE_PASSWORD'] : null
        ],

        'mailer' => [
            'from' => [
                'email' => $_SERVER['MAILER_FROM_EMAIL'],
                'name' => $_SERVER['MAILER_FROM_NAME']
            ], // who should send notification
            'to' => $_SERVER['MAILER_ADMIN_EMAIL'], // who should receive notification
            'reply_to' => $_SERVER['MAILER_ADMIN_EMAIL'], // who should receive responses
            'subject' => $_SERVER['MAILER_SUBJECT'], // subject prefix
            'thankyou_subject' => $_SERVER['MAILER_THANKYOU_SUBJECT'], // full subject
            'host' => $_SERVER['MAILER_HOST'], // Mailcatcher on Vagrant host
            'port' => $_SERVER['MAILER_PORT'],
            'username' => ($_SERVER['MAILER_USERNAME']) ? $_SERVER['MAILER_USERNAME'] : null,
            'password' => ($_SERVER['MAILER_PASSWORD']) ? $_SERVER['MAILER_PASSWORD'] : null
        ],
    ],
];
