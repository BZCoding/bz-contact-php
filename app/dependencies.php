<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    return $logger;
};

// csrf protection
$container['csrf'] = function ($c) {
    // @see https://github.com/slimphp/Slim-Csrf to customize
    $csrf = new Slim\Csrf\Guard();
    return $csrf;
};

// form builder
$container['form'] = function ($c) {
    $settings = $c->get('settings')['form'];

    // Set form file to custom or default
    $formFile = (!empty($settings['file'])) ? $settings['file'] : __DIR__ . '/form.json';
    if (!is_readable($formFile)) {
        throw new \ErrorException(sprintf("Unable to read form file '%s'", $formFile));
    }

    // Load form data from file
    if (($formData = @file_get_contents($formFile)) === false) {
        throw new \ErrorException(sprintf("Unable to load data from file '%s'", $formFile));
    }

    $builder = new BZContact\Form\FormBuilder($formData);
    return $builder;
};

// form validator
$container['validator'] = function ($c) {
    $validator = new BZContact\Form\Validator();
    return $validator;
};

// form store
$container['store'] = function ($c) {
    $settings = $c->get('settings')['database'];
    $client = new Sokil\Mongo\Client($settings['host']);
    $client->useDatabase($settings['name']);
    $collection = $client->getCollection($settings['collection']);
    $store = new BZContact\Form\Store\MongoDBStore($collection, $c->get('logger'));
    return $store;
};

// event dispatcher
$container['dispatcher'] = function ($c) {
    $dispatcher = new Symfony\Component\EventDispatcher\EventDispatcher();
    return $dispatcher;
};

// queue provider
$container['amqp'] = function ($c) {
    $settings = $c->get('settings')['amqp'];
    $amqp = new PhpAmqpLib\Connection\AMQPStreamConnection(
        $settings['host'],
        $settings['port'],
        $settings['username'],
        $settings['password'],
        $settings['vhost']
    );
    return $amqp;
};

// job queue
$container['queue'] = function ($c) {
    $queue = new BZContact\Worker\Queue($c);
    return $queue;
};

// event mailer
$container['mailer'] = function ($c) {
    $mailer = new BZContact\Mailer\QueueMailer($c);
    return $mailer;
};

// newsletter engine
$container['newsletter'] = function ($c) {
    $settings = $c->get('settings')['newsletter'];
    $newsletter = new DrewM\MailChimp\MailChimp($settings['api_key']);
    return $newsletter;
};

// server error handler
$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {

        // Log exception for devs
        $c->get('logger')->error($exception->getMessage());

        // Display nice error template to user
        return $c->get('renderer')->render(
            $response->withStatus(500)->withHeader('Content-Type', 'text/html'),
            'error.phtml',
            ['support' => $c->get('settings')['mailer']['reply_to']]
        );
    };
};

// 404 error handler
$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {

        // Display nice error template to user
        return $c->get('renderer')->render(
            $response->withStatus(404)->withHeader('Content-Type', 'text/html'),
            '404.phtml',
            ['support' => $c->get('settings')['mailer']['reply_to']]
        );
    };
};

// 405 (method not allowed) error handler
$container['notAllowedHandler'] = function ($c) {
    return function ($request, $response, $methods) use ($c) {
        return $c['response']
            ->withStatus(405)
            ->withHeader('Allow', implode(', ', $methods))
            ->withHeader('Content-type', 'text/html')
            ->write('Method must be one of: ' . implode(', ', $methods));
    };
};
