<?php
namespace BZContact;

use PHPUnit\Framework\TestCase;

class QueueTest extends TestCase
{
    public function testNewQueue()
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
            ['amqp', $amqp]
        ]));

        $queue = new Worker\Queue($container);
        $this->assertInstanceOf(Worker\Queue::class, $queue);
        $this->assertTrue($queue->publish(['action' => 'hello'], 'somequeue', 'sometag'));
    }
}
