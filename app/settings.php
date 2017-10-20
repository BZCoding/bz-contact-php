<?php
use Rollbar\Rollbar;
use Rollbar\Payload\Level;

// Set Rollbar error tracking, if available
if (!empty($_SERVER['ROLLBAR_ACCESS_TOKEN'])) {
    Rollbar::init([
        'access_token' => $_SERVER['ROLLBAR_ACCESS_TOKEN'],
        'environment' => $_SERVER['SLIM_MODE']
    ]);
}

// We need to take care of exceptions here, Slim won't catch 'em
try {
    // Stop if database is not configured
    if (empty(getenv('DATABASE_URI'))) {
        throw new \Exception('Missing Database settings');
    }

    // Pre parse AMQP settings
    $amqp = parse_url($_SERVER['AMQP_URL']);

    // If using Postmark fix mailer settings
    if (!empty($_SERVER['POSTMARK_SMTP_SERVER'])) {
        $_SERVER['MAILER_HOST'] = $_SERVER['POSTMARK_SMTP_SERVER'];
    }
    if (!empty($_SERVER['POSTMARK_API_TOKEN'])) {
        $_SERVER['MAILER_USERNAME'] = $_SERVER['POSTMARK_API_TOKEN'];
        $_SERVER['MAILER_PASSWORD'] = $_SERVER['POSTMARK_API_TOKEN'];
    }

    // Load current theme
    $currentTheme = !empty($_SERVER['UI_THEME']) ? $_SERVER['UI_THEME'] : 'default';
    $templatePath = __DIR__ . sprintf('/themes/%s/', $currentTheme);
    if (!is_dir($templatePath)) {
        throw new \Exception('Invalid UI theme');
    }

    // Stop if mailer is not configured
    if (empty($_SERVER['MAILER_HOST'])) {
        throw new \Exception('Missing Mailer settings');
    }
} catch (\Exception $e) {
    http_response_code(500);
    header('Content-type: text/html');

    // Log to syslog o stdout (for Devs)
    // TODO: try this on Heroku
    error_log('[app:error] ' . $e->getMessage(), 0);

    // Send the exception to Rollbar (it's not automatic)
    Rollbar::log(Level::error(), $e);

    if (PHP_SAPI === 'cli') {
        exit(1);
    }

    // Nice message for users
    $errorMessage = '<html>' .
        '<head><title>Internal Server Error</title></head>' .
        '<body>' . "\n" .
        '<h1>Ops, something went wrong!</h1>' . "\n" .
        '<p>For some reason we were not able to process your request, ' .
        'but an administrator has been notified of this.</p>' . "\n" .
        '<p>If you keep seeing this nasty page, please write to %s.</p>' . "\n" .
        '</body></html>' . "\n";
    exit(sprintf(
        $errorMessage,
        (!empty($_SERVER['MAILER_ADMIN_EMAIL']) ? $_SERVER['MAILER_ADMIN_EMAIL'] : '[n/a]')
    ));
}

return [
    'settings' => [
        'displayErrorDetails' => ($_SERVER['SLIM_MODE'] !== 'production') ? true : false, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        'csrfToken' => isset($_SERVER['CSRF_ENABLED'])
            ? (boolean)$_SERVER['CSRF_ENABLED'] : true, // CSRF enabled by default

        'redirect_thankyou' => !empty($_SERVER['REDIRECT_THANKYOU']) ? $_SERVER['REDIRECT_THANKYOU'] : false,

        // Renderer settings
        'renderer' => [
            'template_path' => $templatePath, // Put your custom theme in /themes/<custom>/
            'siteName' => 'BZ Contact' // Customize your name here
        ],

        // Monolog settings
        'logger' => [
            'name' => 'bzcontact',
            'path' => (isset($_SERVER['LOG_PATH'])) ? $_SERVER['LOG_PATH'] : 'php://stdout',
        ],

        'database' => [
            'host' => $_SERVER['DATABASE_URI'], // i.e mongodb://username:password@host:port/dbname
            'name' => trim(parse_url($_SERVER['DATABASE_URI'], PHP_URL_PATH), '/'),
            'collection' => !empty($_SERVER['DATABASE_COLLECTION']) ? $_SERVER['DATABASE_COLLECTION'] : 'entries',
        ],

        'mailer' => [
            'from' => [
                'email' => $_SERVER['MAILER_ADMIN_EMAIL'],
                'name' => $_SERVER['MAILER_FROM_NAME']
            ], // who should send notification
            'to' => $_SERVER['MAILER_ADMIN_EMAIL'], // who should receive notification
            'reply_to' => $_SERVER['MAILER_ADMIN_EMAIL'], // who should receive responses
            'subject' => $_SERVER['MAILER_SUBJECT'], // subject prefix
            'thankyou_subject' => $_SERVER['MAILER_THANKYOU_SUBJECT'], // full subject
            'host' => $_SERVER['MAILER_HOST'], // Mailcatcher on Vagrant host
            'port' => !empty($_SERVER['MAILER_PORT']) ? $_SERVER['MAILER_PORT'] : 25,
            'username' => (isset($_SERVER['MAILER_USERNAME'])) ? $_SERVER['MAILER_USERNAME'] : null,
            'password' => (isset($_SERVER['MAILER_PASSWORD'])) ? $_SERVER['MAILER_PASSWORD'] : null
        ],

        'amqp' => [
            'host' => $amqp['host'],
            'port' => !empty($amqp['port']) ? $amqp['port'] : 5672,
            'username' => $amqp['user'],
            'password' => $amqp['pass'],
            'vhost' => (empty($amqp['path']) || '/' === $amqp['path']) ? '/' : trim($amqp['path'], '/'),
            'queue' => !empty($_SERVER['AMQP_QUEUE']) ? $_SERVER['AMQP_QUEUE'] : 'tasks',
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
