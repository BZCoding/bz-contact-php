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
        $result = $this->store->saveEntry($this->data);
        // Fix for MongoDB '_id' field
        if (isset($this->data['_id']->{'$id'})) {
            $this->data['id'] = $this->data['_id']->{'$id'};
            unset($this->data['_id']);
        }
        return $result;
    }
    public function getData()
    {
        return $this->data;
    }
}
