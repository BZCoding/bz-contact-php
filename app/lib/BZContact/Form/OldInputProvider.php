<?php

namespace BZContact\Form;

use \AdamWathan\Form\OldInput\OldInputInterface;

class OldInputProvider implements OldInputInterface
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function hasOldInput()
    {
        return (!empty($this->data));
    }

    public function getOldInput($key)
    {
        return (isset($this->data[$key])) ? $this->data[$key]: null;
    }
}
