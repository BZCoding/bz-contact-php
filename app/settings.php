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
            'path' => (isset($_SERVER['LOG_PATH'])) ? $_SERVER['LOG_PATH'] : 'php://stdout',
        ],

        'database' => [
            'host' => $_SERVER['DATABASE_HOST'], // i.e mongodb://1.2.3.4:27017
            'name' => $_SERVER['DATABASE_NAME'],
            'collection' => $_SERVER['DATABASE_COLLECTION'],
            'username' => (isset($_SERVER['DATABASE_USERNAME'])) ? $_SERVER['DATABASE_USERNAME'] : null,
            'password' => (isset($_SERVER['DATABASE_PASSWORD'])) ? $_SERVER['DATABASE_PASSWORD'] : null
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
            'username' => (isset($_SERVER['MAILER_USERNAME'])) ? $_SERVER['MAILER_USERNAME'] : null,
            'password' => (isset($_SERVER['MAILER_PASSWORD'])) ? $_SERVER['MAILER_PASSWORD'] : null
        ],

        'amqp' => [
            'host' => $_SERVER['AMQP_HOST'],
            'port' => $_SERVER['AMQP_PORT'],
            'username' => $_SERVER['AMQP_USERNAME'],
            'password' => $_SERVER['AMQP_PASSWORD'],
            'vhost' => $_SERVER['AMQP_VHOST'],
            'queue' => $_SERVER['AMQP_QUEUE'],
        ],

        'newsletter' => [
            'api_key' => !empty($_SERVER['NEWSLETTER_API_KEY']) ? $_SERVER['NEWSLETTER_API_KEY'] : null,
            'list_id' => !empty($_SERVER['NEWSLETTER_LIST_ID']) ? $_SERVER['NEWSLETTER_LIST_ID'] : null,
            'enabled' => !empty($_SERVER['NEWSLETTER_API_KEY']) && !empty($_SERVER['NEWSLETTER_LIST_ID']),
            // Map between MailChimp list fields (keys) and form field names (value)
            // The value can be a function that processes the field value
            'merge_fields' => [
                'FNAME' => [
                    function ($field) {
                        return explode(' ', $field)[0];
                    },
                    'name'
                ],
                'LNAME' => [
                    function ($field) {
                        $f = explode(' ', $field);
                        return !empty($f[1]) ? $f[1] : '';
                    },
                    'name'
                ],
                'SIGNUP' => [
                    'string',
                    isset($_SERVER['NEWSLETTER_MERGE_SIGNUP'])
                        ? $_SERVER['NEWSLETTER_MERGE_SIGNUP'] : 'bzcontact_form'
                ],
                'COMPANY' => 'company'
            ]
        ],

        'webhook' => [
            'url' => !empty($_SERVER['WEBHOOK_URL']) ? $_SERVER['WEBHOOK_URL'] : null,
            'headers' => !empty($_SERVER['WEBHOOK_HEADERS']) ? $_SERVER['WEBHOOK_HEADERS'] : null,
            'enabled' => !empty($_SERVER['WEBHOOK_URL'])
        ],
    ],
];
