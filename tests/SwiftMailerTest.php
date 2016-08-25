<?php
namespace BZContact;

use PHPUnit\Framework\TestCase;

class SwiftMailerTest extends TestCase
{
    public function testNewMailer()
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $logger->expects($this->any())->method('info')->willReturn(true);

        $swiftMailer = $this->createMock(\Swift_Mailer::class);
        $swiftMailer->expects($this->any())->method('send')->willReturn(1);

        $mailer = new Mailer\SwiftMailer($swiftMailer, $logger);
        $this->assertInstanceOf(Mailer\SwiftMailer::class, $mailer);
        $this->assertTrue($mailer->send([
            'from' => 'foo@example.com',
            'to' => 'bar@example.com',
            'reply_to' => 'baz@example.com',
            'subject' => 'Hello',
            'body' => 'World!'
        ]));
    }
}
