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
$container['store'] = function ($c) use ($container) {
    $store = new BZContact\Form\Store\DummyStore($container['logger']);
    return $store;
};

// error handler
$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {

        // Log exception for devs
        $c->get('logger')->error($exception->getMessage());

        // Display nice error template to user
        // TODO better error/exception template
        return $c['response']->withStatus(500)
                             ->withHeader('Content-Type', 'text/html')
                             ->write('Something went wrong!');
    };
};
