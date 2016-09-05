<?php
namespace BZContact\Mailer;

use Psr\Log\LoggerInterface;
use Interop\Container\ContainerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Queue Mailer
 *
 * Messages are saved in a AMQP compatible queue and
 * consumed later by a worker.
 */
class QueueMailer implements MailerInterface
{
    protected $container = null;
    protected $logger = null;
    protected $amqp = null;

    /**
     * Construct a Queue Mailer
     *
     * @param ContainerInterface $container The container object
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get('logger');
        $this->amqp = $container->get('amqp');
        $this->container = $container;
    }

    public function __destruct()
    {
        $this->amqp->close();
    }

    /**
     * Mock a send message action
     *
     * @param array $data Array of message data
     * @return boolean
     */
    public function send(array $data)
    {
        if (empty($data['action'])) {
            $data['action'] = 'send-email-message';
        }

        $this->logger->info("Enqueueing message", ['id' => $data['id']]);

        // Get queue provider
        $channel = $this->amqp->channel();

        // Declare a durable queue (3rd arg set to TRUE)
        $queue = $this->container->get('settings')['amqp']['queue'];
        $channel->queue_declare($queue, false, true, false, false);

        // Create a persistent message payload (delivery_mode = 2):
        // the message is removed only when the consumer sends an ACK signal
        $payload = [
            'action' => $data['action'],
            'message' => ['id' => $data['id']]
        ];
        $msg = new AMQPMessage(json_encode($payload), ['delivery_mode' => 2]);

        // Publish the message to queue with an empty consumer tag
        $channel->basic_publish($msg, '', $queue);

        // Close the channels
        $channel->close();

        return true;
    }

    public function sendAdminNotification(array $message)
    {
        $this->logger->info("Enqueueing admin notification", ['message' => $message['id']]);
        $message['action'] = 'send-admin-notification';
        return $this->send($message);
    }

    public function sendSubscriberNotification(array $message)
    {
        $this->logger->info("Enqueueing subscriber notification", ['message' => $message['id']]);
        $message['action'] = 'send-subscriber-notification';
        return $this->send($message);
    }
}
