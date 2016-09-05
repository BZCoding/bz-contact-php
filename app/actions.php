<?php
/**
 * Worker Actions
 */

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
