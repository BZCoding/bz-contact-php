<?php
namespace BZContact;

use PHPUnit\Framework\TestCase;

class MessageSavedEventTest extends TestCase
{
    public function testEvent()
    {
        $event = new Form\Event\MessageSavedEvent(['foo' => 'bar']);
        $this->assertInstanceOf(Form\Event\MessageSavedEvent::class, $event);

        $message = $event->getMessage();
        $this->assertInternalType('array', $message);
    }
}
