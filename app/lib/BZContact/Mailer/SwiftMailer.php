<?php
namespace BZContact\Mailer;

use Psr\Log\LoggerInterface;
use Interop\Container\ContainerInterface;
use Swift_Mailer;
use Swift_Message;

class SwiftMailer implements MailerInterface
{
    protected $container = null;
    protected $logger = null;
    protected $mailer = null;

    /**
     * Construct a Dummy Mailer
     *
     * @param Swift_Mailer $mailer The swift mailer object
     * @param ContainerInterface $container The container object
     * @return void
     */
    public function __construct(Swift_Mailer $mailer, ContainerInterface $container)
    {
        $this->mailer = $mailer;
        $this->logger = $container->get('logger');
        $this->container = $container;
    }

    /**
     * Send a generic TXT email message
     *
     * @param array $data Array of message data
     * @return boolean
     */
    public function send(array $data)
    {
        $message = Swift_Message::newInstance($data['subject'])
            ->setFrom($data['from'])
            ->setTo($data['to'])
            ->setReplyTo($data['reply_to'])
            ->setBody($data['body']);
        try {
            $result = $this->mailer->send($message);
            return ($result > 0);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }

    /**
     * Send an email notification to the site admin
     *
     * @param array $message Array of message data
     * @return boolean
     */
    public function sendAdminNotification(array $message)
    {
        $this->logger->info("Sending admin notification", ['message' => $message['id']]);
        $settings = $this->container->get('settings')['mailer'];
        return $this->send([
            'from' => [
                $settings['from']['email'] => !empty($message['name']) ? $message['name'] : 'Mailer at BZ Contact'
            ],
            'to' => $settings['to'],
            'reply_to' => $message['email'],
            'subject' => $settings['subject'] . ' '
                . (!empty($message['subject']) ? $message['subject'] : 'New form submission'),
            'body' => $this->container->get('renderer')->fetch('email/entry.txt', ['entry' => $message])
        ]);
    }

    /**
     * Send an email notification to the subscriber
     *
     * @param array $message Array of message data
     * @return boolean
     */
    public function sendSubscriberNotification(array $message)
    {
        $this->logger->info("Sending subscriber notification", ['message' => $message['id']]);
        $settings = $this->container->get('settings')['mailer'];
        return $this->send([
            'from' => [$settings['from']['email'] => $settings['from']['name']],
            'to' => !empty($message['name']) ? [$message['email'] => $message['name']] : $message['email'],
            'reply_to' => $settings['reply_to'],
            'subject' => $settings['thankyou_subject'],
            'body' => $this->container->get('renderer')->fetch(
                'email/thankyou.txt',
                [
                    'name' => !empty($message['name']) ? explode(' ', $message['name'])[0] : ''
                ] // Pass only the first name
            )
        ]);
    }
}
