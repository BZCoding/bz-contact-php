<?php
namespace BZContact\Worker;

use Interop\Container\ContainerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Wrapper for an AMQP queue
 */
class Queue
{
    protected $logger = null;
    protected $amqp = null;

    /**
     * Construct a Queue
     *
     * @param ContainerInterface $container The container object
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get('logger');
        $this->amqp = $container->get('amqp');
    }

    public function __destruct()
    {
        $this->amqp->close();
    }

    /**
     * Publish a persistent message payload to a queue
     *
     * @param  array  $message  Associative array with message payload
     * @param  string $queue    Name of the queue to publish
     * @param  string $tag      Consumer tag for the message
     * @return boolean
     */
    public function publish($message, $queue, $tag = '')
    {
        // Get queue provider
        $channel = $this->amqp->channel();

        // Declare a durable queue (3rd arg set to TRUE)
        $channel->queue_declare($queue, false, true, false, false);

        // Create a persistent message payload (delivery_mode = 2):
        // the message is removed only when the consumer sends an ACK signal
        $msg = new AMQPMessage(json_encode($message), ['delivery_mode' => 2]);

        // Publish the message to queue with an empty consumer tag
        $channel->basic_publish($msg, $tag, $queue);

        // Close the channels
        $channel->close();
        return true;
    }
}
