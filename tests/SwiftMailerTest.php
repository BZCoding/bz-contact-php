<?php
namespace BZContact;

use PHPUnit\Framework\TestCase;

class SwiftMailerTest extends TestCase
{
    public function testNewMailer()
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $logger->method('info')->willReturn(true);

        $renderer = $this->createMock(\Slim\Views\PhpRenderer::class);
        $renderer->method('fetch')->willReturn('Hello World');

        $container = $this->createMock(\Interop\Container\ContainerInterface::class);
        $container->method('get')->will($this->returnValueMap([
            ['logger', $logger],
            ['renderer', $renderer],
            ['settings', ['mailer' => [
                'from' => ['email' => 'admin@foo.com', 'name' => 'Administrator'],
                'to' => 'admin@foo.com',
                'reply_to' => 'subscriber@somewhere.com',
                'subject' => '[PREFIX]',
                'thankyou_subject' => 'Thank you'
            ]]]
        ]));

        $swiftMailer = $this->createMock(\Swift_Mailer::class);
        $swiftMailer->expects($this->any())->method('send')->willReturn(1);

        $message = [
            'id' => '1234Abc',
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'subject' => 'Hello',
            'message' => 'World',
            'from' => 'foo@example.com',
            'to' => 'bar@example.com',
            'reply_to' => 'baz@example.com',
            'subject' => 'Hello',
            'body' => 'World!'
        ];

        $mailer = new Mailer\SwiftMailer($swiftMailer, $container);
        $this->assertInstanceOf(Mailer\SwiftMailer::class, $mailer);
        $this->assertTrue($mailer->sendAdminNotification($message));
        $this->assertTrue($mailer->sendSubscriberNotification($message));
    }
}
