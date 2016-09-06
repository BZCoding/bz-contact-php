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
            if (is_array($value) && is_callable($value[0]) && !empty($value[1])) {
                $mergeFields[$key] = $value[0]($message[$value[1]]);
                continue;
            }
            // Field to string
            if (is_array($value) && 2 == count($value) && 'string' === $value[0]) {
                $mergeFields[$key] = (string) $value[1];
                continue;
            }
            // Field to form field
            if (!empty($message[$value])) {
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

        // Always return true and log the errors, else the queue is blocked
        return true;
    };
};
