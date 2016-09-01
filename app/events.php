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
