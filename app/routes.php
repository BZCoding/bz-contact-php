<?php
// Routes

$app->get('/', function ($request, $response) {
    // Sample log message
    $this->logger->info("GET - '/'");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', [
        'form' => $this->form
    ]);
});

$app->post('/', function ($request, $response) {
    // Sample log message
    $this->logger->info("POST - '/'");
    $this->logger->info("POST - '/'", ['ip' => $request->getAttribute('ip_address')]);

    // Render index view
    return $this->renderer->render($response, 'index.phtml', [
        'form' => $this->form
    ]);
});
