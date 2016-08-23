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
    $this->logger->info("POST - '/'", ['ip' => $request->getAttribute('ip_address')]);

    // Sanitize POSTed data
    $data = filter_var_array($request->getParsedBody(), FILTER_SANITIZE_STRING);
    $this->logger->debug("POSTed", ['data' => $data]);

    $this->form->setOldInputProvider(new BZContact\Form\OldInputProvider($data));

    // Validate form data
    if ($this->form->validates($data, $this->validator)) {
        // Save entry to database

        // A Form\Store\StoreInterface object creates a Form\Store\EntryInterface object
        $entry = $this->store->createEntry($data);

        // It can throw exception, catched by the error handler
        $entry->save();

        // Do or enqueue addictional actions/hooks:
        //  - send message to owner
        //  - send message to user
        //  - other hooks (newsletter, webhook)

        // Redirect to thank you
        // return $response->withStatus(302)->withHeader('Location', 'http://example.com/thankyou.html');
        return $this->renderer->render($response, 'thankyou.phtml');
    }
    $this->form->setErrorStore(new BZContact\Form\ErrorStore($this->validator->errors()));
    // var_dump($this->validator->errors());

    // (default) Render index view, with errors if present
    return $this->renderer->render($response, 'index.phtml', [
        'form' => $this->form
    ]);
});
