<?php
namespace BZContact\Form\Event;

use Symfony\Component\EventDispatcher\Event;

class MessageSavedEvent extends Event
{
    const NAME = 'message.saved';

    protected $message;

    public function __construct(array $message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
