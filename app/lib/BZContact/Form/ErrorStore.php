<?php

namespace BZContact\Form;

use \AdamWathan\Form\ErrorStore\ErrorStoreInterface;

class ErrorStore implements ErrorStoreInterface
{
    private $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    public function hasError($key)
    {
        return !empty($this->errors[$key]);
    }

    public function getError($key)
    {
        if (!$this->hasError($key)) {
            return null;
        }
        return $this->errors[$key][0];
    }
}
