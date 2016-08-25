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
            'from' => [
                'email' => 'hello@example.com',
                'name' => 'BZ Contact at Example Inc'
            ], // who should send notification
            'to' => 'admin@example.com', // who should receive notification
            'reply_to' => 'admin@example.com', // who should receive responses
            'subject' => '[BZ Contact] ', // subject prefix
            'host' => '10.0.2.2', // Mailcatcher on Vagrant host
            'port' => 1025,
            'username' => 'foo',
            'password' => 'bar'
        ],
    ],
];
