<?php
/**
 * Worker Actions
 */

// Send a notification message to the application owner
$container['action-send-admin-notification'] = function ($c) {
    return function ($args) use ($c) {
        $mailer = $c->get('mailer');
        $store = $c->get('store');

        // Check that payload is valid for this action
        if (empty($args['message'])) {
            throw new \InvalidArgumentException("Missing argument 'message'");
        }
        if (empty($args['message']['id'])) {
            throw new \InvalidArgumentException("Missing argument 'message.id'");
        }

        $message = $store->getEntry($args['message']['id']);

        // Send message to application owner
        return $mailer->sendAdminNotification($message);
    };
};

// Send a thank you message to the user
$container['action-send-subscriber-notification'] = function ($c) {
    return function ($args) use ($c) {
        $mailer = $c->get('mailer');
        $store = $c->get('store');

        // Check that payload is valid for this action
        if (empty($args['message'])) {
            throw new \InvalidArgumentException("Missing argument 'message'");
        }
        if (empty($args['message']['id'])) {
            throw new \InvalidArgumentException("Missing argument 'message.id'");
        }

        $message = $store->getEntry($args['message']['id']);

        // Sent thank you message to user
        return $mailer->sendSubscriberNotification($message);
    };
};

// Subscribe the user to your newsletter of choice
$container['action-newsletter-subscribe'] = function ($c) {
    return function ($args) use ($c) {
        $newsletter = $c->get('newsletter');
        $logger = $c->get('logger');
        $store = $c->get('store');
        $settings = $c->get('settings')['newsletter'];

        // Check that payload is valid for this action
        if (empty($args['message'])) {
            throw new \InvalidArgumentException("Missing argument 'message'");
        }
        if (empty($args['message']['id'])) {
            throw new \InvalidArgumentException("Missing argument 'message.id'");
        }

        // Fetch message data
        $message = $store->getEntry($args['message']['id']);

        $listId = $settings['list_id'];

        // Parse merge fields
        $mergeFields = [];
        foreach ($settings['merge_fields'] as $key => $value) {
            // Field to function
            if (is_array($value) && is_callable($value[0]) && !empty($value[1]) && !empty($message[$value[1]])) {
                $mergeFields[$key] = $value[0]($message[$value[1]]);
                continue;
            }
            // Field to string
            if (is_array($value) && 2 == count($value) && 'string' === $value[0]) {
                $mergeFields[$key] = (string) $value[1];
                continue;
            }
            // Field to form field
            if (is_string($value) && !empty($message[$value])) {
                $mergeFields[$key] = (string) $message[$value];
            }
        }
        $result = $newsletter->post(
            "lists/$listId/members",
            [
                'email_address' => $message['email'],
                'status'        => 'subscribed',
                'merge_fields' => $mergeFields,
            ]
        );

        // Call the newsletter provider
        switch ($result['status']) {
            case 'subscribed':
                $logger->info('Newsletter - User subscribed successfully', [
                    'user_id' => $result['id'],
                    'list_id' => $result['list_id']
                ]);
                break;
            default:
                $logger->notice('Newsletter - Unable to subscribe user', ['result' => $result]);
                break;
        }

        // For now we always return true and log the error
        // TODO: determine which errors deserve a retry and return false to requeue
        return true;
    };
};

// Call a web hook
$container['action-webhook-post'] = function ($c) {
    return function ($args) use ($c) {
        $logger = $c->get('logger');
        $store = $c->get('store');

        // Check that payload is valid for this action
        if (empty($args['message'])) {
            throw new \InvalidArgumentException("Missing argument 'message'");
        }
        if (empty($args['message']['id'])) {
            throw new \InvalidArgumentException("Missing argument 'message.id'");
        }
        if (empty($args['url'])) {
            throw new \InvalidArgumentException("Missing argument 'url'");
        }

        $message = $store->getEntry($args['message']['id']);

        // Parse custom headers
        $headers = [];
        if (!empty($args['headers'])) {
            $hs = explode('|', $args['headers']);
            if (is_array($hs) && !empty($hs)) {
                foreach ($hs as $header) {
                    $h = explode(':', $header);
                    if (is_array($h) && !empty($h[1])) {
                        $headers[$h[0]] = $h[1];
                    }
                }
            }
        }

        $logger->info('Calling web URL', ['url' => $args['url']]);
        $client = new GuzzleHttp\Client();
        $request = new GuzzleHttp\Psr7\Request('POST', $args['url']);

        $payload = [];
        if (!empty($args['message']['action'])) {
            $payload['action'] = $args['message']['action'];
        }
        $payload['created_at'] = date('Y-m-d H:i:s');
        $payload['data'] = $message;

        try {
            $response = $client->send($request, [
                'timeout' => 15,
                'json' => $payload,
                'headers' => $headers
            ]);

            $status = $response->getStatusCode();
            switch ($status) {
                case 200:
                    $logger->info('Webhook - Posted successfully');
                    break;
                default:
                    $logger->error('Webhook - Remote server error', ['status' => $status]);
                    break;
            }

            // For now we always return true and log the error
            // TODO: determine which errors deserve a retry and return false to requeue
            return true;
        } catch (GuzzleHttp\Exception\RequestException $e) {
            $logger->error('Webhook - Timeout expired, retrying later');
            return false;
        }
    };
};
