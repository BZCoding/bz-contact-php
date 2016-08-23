<?php

namespace BZContact\Form\Store;

use Psr\Log\LoggerInterface;

class FormEntry implements FormEntryInterface
{
    protected $store = null;
    protected $data = [];

    /**
     * Construct a Basic form entry
     *
     * @param array $data Array of user data
     * @param StoreInterface $store The store object
     * @return void
     */
    public function __construct(array $data, StoreInterface $store)
    {
        $this->store = $store;
        $this->data = $data;
    }

    /**
     * Save an entry to database
     *
     * @throw \Exceptiom
     * @return boolean
     */
    public function save()
    {
        return $this->store->saveEntry($this->data);
    }
}
