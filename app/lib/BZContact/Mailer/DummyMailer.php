<?php
namespace BZContact\Mailer;

use Psr\Log\LoggerInterface;

class DummyMailer implements MailerInterface
{
    protected $logger = null;

    /**
     * Construct a Dummy Mailer
     *
     * @param LoggerInterface $logger The logger object
     * @return void
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
        return true;
    }
}
