<?php
// Routes

// Render ToS page
$app->get('/terms', function ($request, $response) {
    return $this->renderer->render($response, 'terms.phtml', [
        'pageTitle' => 'Terms of Service'
    ]);
});

// Render Privacy page
$app->get('/privacy', function ($request, $response) {
    return $this->renderer->render($response, 'privacy.phtml', [
        'pageTitle' => 'Privacy & Cookies'
    ]);
});

// Render main page
$app->get('/', function ($request, $response) {
    return $this->renderer->render($response, 'index.phtml', [
        'form' => $this->form
    ]);
});

// Process form entry
$app->post('/', function ($request, $response) {
    // Sample log message
    $this->logger->info("POST - '/'", ['ip' => $request->getAttribute('ip_address')]);

    // Sanitize POSTed data
    foreach ($request->getParsedBody() as $key => $value) {
        $data[$key] = filter_var($value, FILTER_SANITIZE_STRING, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
    }
    $this->logger->debug("POSTed", ['data' => $data]);

    $this->form->setOldInputProvider(new BZContact\Form\OldInputProvider($data));

    // Validate form data
    if ($this->form->validates($data, $this->validator)) {
        // Filter data that we don't want to be saved in database
        $data = $this->form->filter($data);

        // Add IP address and timestamp
        $data['ip'] = $request->getAttribute('ip_address');
        $data['datetime'] = date('Y-m-d H:i:s');

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
        if (($thankyou = $this->get('settings')['redirect_thankyou']) && filter_var($thankyou, FILTER_VALIDATE_URL)) {
            return $response->withStatus(302)->withHeader('Location', $thankyou);
        }
        return $this->renderer->render($response, 'thankyou.phtml');
    }
    $this->form->setErrorStore(new BZContact\Form\ErrorStore($this->validator->errors()));

    // (default) Render index view, with errors if present
    return $this->renderer->render($response, 'index.phtml', [
        'form' => $this->form
    ]);
});
