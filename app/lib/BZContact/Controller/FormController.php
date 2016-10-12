<?php
namespace BZContact\Controller;

use Interop\Container\ContainerInterface;
use BZContact\Form\OldInputProvider;
use BZContact\Form\Event\MessageSavedEvent;
use BZContact\Form\ErrorStore;

class FormController
{
    protected $ci;
    protected $settings;
    protected $logger;
    protected $view;
    protected $form;
    protected $validator;
    protected $store;
    protected $dispatcher;

    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
        $this->settings = $this->ci->get('settings');
        $this->logger = $this->ci->get('logger');
        $this->view = $this->ci->get('renderer');
        $this->form = $this->ci->get('form');
        $this->validator = $this->ci->get('validator');
        $this->store = $this->ci->get('store');
        $this->dispatcher = $this->ci->get('dispatcher');
    }

    public function __invoke($request, $response, $args)
    {
        // Sample log message
        $this->logger->info("POST - '/'", ['ip' => $request->getAttribute('ip_address')]);

        // Sanitize POSTed data
        foreach ($request->getParsedBody() as $key => $value) {
            $data[$key] = filter_var($value, FILTER_SANITIZE_STRING, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
        }
        $this->logger->debug("POSTed", ['data' => $data]);

        $this->form->setOldInputProvider(new OldInputProvider($data));

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
                MessageSavedEvent::NAME,
                new MessageSavedEvent($data)
            );

            // Redirect to thank you
            if (($thankyou = $this->settings['redirect_thankyou']) && filter_var($thankyou, FILTER_VALIDATE_URL)) {
                return $response->withStatus(302)->withHeader('Location', $thankyou);
            }
            return $this->view->render($response, 'thankyou.phtml');
        }
        $this->form->setErrorStore(new ErrorStore($this->validator->errors()));

        // (default) Render index view, with errors if present
        return $this->view->render($response, 'index.phtml', [
            'form' => $this->form
        ]);
    }
}
