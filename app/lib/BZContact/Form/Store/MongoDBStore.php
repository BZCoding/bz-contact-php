<?php

namespace BZContact\Form\Store;

use Psr\Log\LoggerInterface;
use Sokil\Mongo\Collection;

class MongoDBStore implements StoreInterface
{
    protected $logger = null;
    protected $client = null;
    protected $collection = null;

    /**
     * Construct a Dummy Store
     *
     * @param LoggerInterface $logger The logger object
     * @return void
     */
    public function __construct(Collection $collection, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->collection = $collection;
    }

    /**
     * Create a new form entry with given form data
     *
     * @param array $data Array of user data
     * @return FormEntryInterface
     */
    public function createEntry(array $data)
    {
        return new FormEntry($data, $this);
    }

    /**
     * Save a form entry to a MongoDB collection
     *
     * @param array $data Array of form data
     * @return boolean
     * @throws Sokil\Mongo\Exception
     */
    public function saveEntry(array $data)
    {
        $this->collection->insert($data);
        return true;
    }
}
