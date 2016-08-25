<?php

namespace BZContact\Form\Store;

interface FormEntryInterface
{
    public function save();
    public function getData();
}
