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
$dispatcher->addListener(MessageSavedEvent::NAME, function (Event $event) use ($container) {
    // Will be executed when the a message has been successfully saved to store

    $logger = $container->get('logger');
    $message = $event->getMessage();

    $amqp = $container->get('amqp');
    $action = 'newsletter-subscribe';
    $logger->info("Enqueueing action", ['action' => $action]);

    // Get queue provider
    $channel = $amqp->channel();

    // Declare a durable queue (3rd arg set to TRUE)
    $queue = $container->get('settings')['amqp']['queue'];
    $channel->queue_declare($queue, false, true, false, false);
    // Create a persistent message payload (delivery_mode = 2):
    // the message is removed only when the consumer sends an ACK signal
    $payload = [
        'action' => $action,
        'message' => ['id' => $message['id']]
    ];
    $msg = new \PhpAmqpLib\Message\AMQPMessage(json_encode($payload), ['delivery_mode' => 2]);

    // Publish the message to queue with an empty consumer tag
    $channel->basic_publish($msg, '', $queue);

    // Close the channels
    $channel->close();
    $amqp->close();
});
