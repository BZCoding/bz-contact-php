<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../../vendor/autoload.php';

// Load Environment
$envFile = dirname(__FILE__) . '/../../.env';
if (is_readable($envFile)) {
    $dotenv = new Dotenv\Dotenv(dirname($envFile));
    $dotenv->load();
}

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../dependencies.php';

// Load global helper functions
require __DIR__ . '/../helpers.php';

// Register middleware
require __DIR__ . '/../middleware.php';

// Register events
require __DIR__ . '/../events.php';

// Register routes
require __DIR__ . '/../routes.php';

// Run app
$app->run();
