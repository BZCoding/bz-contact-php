<?php
return [
    'settings' => [
        'displayErrorDetails' => ($_ENV['SLIM_MODE'] !== 'production') ? true : false, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/views/default/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'bz-contact',
            'path' => ($_ENV['LOG_PATH']) ? $_ENV['LOG_PATH'] : 'php://stdout',
        ],

        'database' => [
            'host' => $_ENV['DATABASE_HOST'], // i.e mongodb://1.2.3.4:27017
            'name' => $_ENV['DATABASE_NAME'],
            'collection' => $_ENV['DATABASE_COLLECTION'],
            'username' => $_ENV['DATABASE_USERNAME'],
            'password' => $_ENV['DATABASE_PASSWORD']
        ],

        'mailer' => [
            'from' => [
                'email' => $_ENV['MAILER_FROM_EMAIL'],
                'name' => $_ENV['MAILER_FROM_NAME']
            ], // who should send notification
            'to' => $_ENV['MAILER_ADMIN_EMAIL'], // who should receive notification
            'reply_to' => $_ENV['MAILER_ADMIN_EMAIL'], // who should receive responses
            'subject' => $_ENV['MAILER_SUBJECT'], // subject prefix
            'host' => $_ENV['MAILER_HOST'], // Mailcatcher on Vagrant host
            'port' => $_ENV['MAILER_PORT'],
            'username' => $_ENV['MAILER_USERNAME'],
            'password' => $_ENV['MAILER_PASSWORD']
        ],
    ],
];
