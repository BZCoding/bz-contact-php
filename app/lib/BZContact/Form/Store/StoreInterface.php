<?php

namespace BZContact\Form\Store;

interface StoreInterface
{
    public function createEntry(array $data);
    public function saveEntry(array $data);
    public function getEntry($id);
}
