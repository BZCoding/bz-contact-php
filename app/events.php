<?php
/**
 * Application Events
 *
 * Uses Symfony Event Dispatcher
 * @see https://symfony.com/doc/current/components/event_dispatcher.html
 */

namespace BZContact\Form;

use Symfony\Component\EventDispatcher\Event;
use BZContact\Form\Event\MessageSavedEvent;

$container = $app->getContainer();
$dispatcher = $container->get('dispatcher');

// e.g. Add Subscriber
// $dispatcher->addSubscriber(new My\Event\Subscriber);

// e.g. Add Listener
// $dispatcher->addListener('foo.action', function (Event $event) use ($container) {
//     // will be executed when the foo.action event is dispatched
// });

// Add default event listener
$dispatcher->addListener(MessageSavedEvent::NAME, function (Event $event) use ($container) {
    // Will be executed when the a message has been successfully saved to store

    $logger = $container->get('logger');
    $message = $event->getMessage();
    $logger->info('Event - Message saved', ['id' => $message['id']]);

    $mailer = $container->get('mailer');

    // Send message to application owner
    $mailer->sendAdminNotification($message);

    // Sent thank you message to user
    $mailer->sendSubscriberNotification($message);
});

// Add newsletter event listener
if ($container->get('settings')['newsletter']['enabled']) {
    $dispatcher->addListener(MessageSavedEvent::NAME, function (Event $event) use ($container) {
        // Will be executed when the a message has been successfully saved to store

        $logger = $container->get('logger');
        $message = $event->getMessage();
        $action = 'newsletter-subscribe';
        $logger->info("Enqueueing action", ['action' => $action]);

        $queue = $container->get('settings')['amqp']['queue'];
        $payload = [
            'action' => $action,
            'message' => ['id' => $message['id']]
        ];
        $container->get('queue')->publish($payload, $queue);
    });
}

// Add a webkook event listener
if ($container->get('settings')['webhook']['enabled']) {
    $dispatcher->addListener(MessageSavedEvent::NAME, function (Event $event) use ($container) {
        // Will be executed when the a message has been successfully saved to store

        $logger = $container->get('logger');
        $message = $event->getMessage();
        $action = 'webhook-post';
        $logger->info("Enqueueing action", ['action' => $action]);

        $queue = $container->get('settings')['amqp']['queue'];
        $payload = [
            'action' => $action,
            'url' => $container->get('settings')['webhook']['url'],
            'headers' => implode('|', ['X-BZContact-Event:message', 'X-BZContact-Delivery:' . uniqid('', true)])
                . '|' . $container->get('settings')['webhook']['headers'],
            'message' => [
                'id' => $message['id'],
                'action' => 'saved'
            ]
        ];
        $container->get('queue')->publish($payload, $queue);
    });
}
