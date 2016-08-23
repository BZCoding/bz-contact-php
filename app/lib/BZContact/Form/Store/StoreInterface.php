<?php

namespace BZContact\Form\Store;

interface StoreInterface
{
    public function createEntry(array $data);
}
