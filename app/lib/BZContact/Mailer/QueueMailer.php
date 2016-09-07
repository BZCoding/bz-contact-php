<?php
namespace BZContact\Mailer;

use Interop\Container\ContainerInterface;

/**
 * Queue Mailer
 *
 * Messages are saved in a AMQP compatible queue and
 * consumed later by a worker.
 */
class QueueMailer implements MailerInterface
{
    protected $logger = null;
    protected $tasks = null;
    protected $queue = null;

    /**
     * Construct a Queue Mailer
     *
     * @param ContainerInterface $container The container object
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get('logger');
        $this->tasks = $container->get('queue');
        $this->queue = $container->get('settings')['amqp']['queue'];
    }

    /**
     * Send message to a queue
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
        $payload = [
            'action' => $data['action'],
            'message' => ['id' => $data['id']]
        ];
        $this->tasks->publish($payload, $this->queue);
        return true;
    }

    /**
     * Send an email notification to admin
     *
     * @param array $message Array of message data
     * @return boolean
     */
    public function sendAdminNotification(array $message)
    {
        $this->logger->info("Enqueueing admin notification", ['message' => $message['id']]);
        $message['action'] = 'send-admin-notification';
        return $this->send($message);
    }

    /**
     * Send an email notification to the subscriber
     *
     * @param array $message Array of message data
     * @return boolean
     */
    public function sendSubscriberNotification(array $message)
    {
        $this->logger->info("Enqueueing subscriber notification", ['message' => $message['id']]);
        $message['action'] = 'send-subscriber-notification';
        return $this->send($message);
    }
}
