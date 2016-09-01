<?php
namespace BZContact;

use PHPUnit\Framework\TestCase;

class QueueMailerTest extends TestCase
{
    public function testNewMailer()
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $logger->method('info')->willReturn(true);

        $channel = $this->createMock(\PhpAmqpLib\Channel\AMQPChannel::class);
        $channel->method('queue_declare')->willReturn(true);
        $channel->method('basic_publish')->willReturn(true);
        $channel->method('close')->willReturn(true);

        $amqp = $this->createMock(\PhpAmqpLib\Connection\AMQPStreamConnection::class);
        $amqp->method('channel')->willReturn($channel);
        $amqp->method('close')->willReturn(true);

        $container = $this->createMock(\Interop\Container\ContainerInterface::class);
        $container->method('get')->will($this->returnValueMap([
            ['logger', $logger],
            ['amqp', $amqp],
            ['settings', [
                'amqp' => ['queue' => 'tasks']
            ]]
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

        $mailer = new Mailer\QueueMailer($container);
        $this->assertInstanceOf(Mailer\QueueMailer::class, $mailer);
        $this->assertTrue($mailer->send($message));
        $this->assertTrue($mailer->sendAdminNotification($message));
        $this->assertTrue($mailer->sendSubscriberNotification($message));
    }
}
