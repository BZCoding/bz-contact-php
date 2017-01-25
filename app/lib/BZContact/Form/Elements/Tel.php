<?php

namespace BZContact\Form\Elements;

use AdamWathan\Form\Elements;

class Tel extends Elements\Text
{
    public function __construct($name)
    {
        parent::__construct($name);
    }

    protected $attributes = [
        'type' => 'tel',
    ];
}
