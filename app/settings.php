<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/views/default/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'bz-contact',
            // 'path' => 'php://stderr',
            'path' => '/tmp/bzcontact.log',
        ],

        'mailer' => [
            'from' => 'hello@example.com', // who should send notification
            'to' => 'admin@example.com', // who should receive notification
            'reply_to' => 'admin@example.com', // who should receive responses
            'subject' => '[BZ Contact] ', // subject prefix
        ],
    ],
];
