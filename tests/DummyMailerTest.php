<?php
namespace BZContact;

use PHPUnit\Framework\TestCase;

class DummyMailerTest extends TestCase
{
    public function testNewMailer()
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $logger->expects($this->any())->method('info')->willReturn(true);

        $message = ['id' => 'abc'];
        $mailer = new Mailer\DummyMailer($logger);
        $this->assertInstanceOf(Mailer\DummyMailer::class, $mailer);
        $this->assertTrue($mailer->sendAdminNotification($message));
        $this->assertTrue($mailer->sendSubscriberNotification($message));
    }
}
