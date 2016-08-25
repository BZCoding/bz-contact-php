<?php
namespace BZContact\Mailer;

use Psr\Log\LoggerInterface;
use Swift_Mailer;
use Swift_Message;

class SwiftMailer implements MailerInterface
{
    protected $logger = null;
    protected $mailer = null;

    /**
     * Construct a Dummy Mailer
     *
     * @param LoggerInterface $logger The logger object
     * @return void
     */
    public function __construct(Swift_Mailer $mailer, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->mailer = $mailer;
    }

    /**
     * Mock a send message action
     *
     * @param array $data Array of message data
     * @return boolean
     */
    public function send(array $data)
    {
        $this->logger->info("Sending Mail", ['data' => $data]);
        $message = Swift_Message::newInstance($data['subject'])
            ->setFrom($data['from'])
            ->setTo($data['to'])
            ->setReplyTo($data['reply_to'])
            ->setBody($data['body']);
        $result = $this->mailer->send($message);
        return ($result > 0);
    }
}
