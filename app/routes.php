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
        // Filter data that we don't want to be saved in database
        $data = $this->form->filter($data);

        // Save entry to database

        // A Form\Store\StoreInterface object creates a Form\Store\FormEntryInterface object
        $entry = $this->store->createEntry($data);

        // It can throw exception, catched by the error handler
        $entry->save();
        $data = $entry->getData();

        // Notify a 'message.saved' event to registered listeners
        // (i.e owner/user notification, newsletter subscription, webhooks, etc)
        $this->dispatcher->dispatch(
            BZContact\Form\Event\MessageSavedEvent::NAME,
            new BZContact\Form\Event\MessageSavedEvent($data)
        );

        // Redirect to thank you
        // return $response->withStatus(302)->withHeader('Location', 'http://example.com/thankyou.html');
        return $this->renderer->render($response, 'thankyou.phtml');
    }
    $this->form->setErrorStore(new BZContact\Form\ErrorStore($this->validator->errors()));

    // (default) Render index view, with errors if present
    return $this->renderer->render($response, 'index.phtml', [
        'form' => $this->form
    ]);
});
