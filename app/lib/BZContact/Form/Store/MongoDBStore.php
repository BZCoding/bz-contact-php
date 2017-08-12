<?php

namespace BZContact\Form\Store;

use Psr\Log\LoggerInterface;
use MongoDB\Collection;
use MongoDB\BSON\ObjectID;

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
     * @return array
     * @throws MongoDB\Exception\InvalidArgumentException
     * @throws MongoDB\Driver\Exception\RuntimeException
     * @throws MongoDB\Driver\Exception\BulkWriteException
     */
    public function saveEntry(array $data)
    {
        $res = $this->collection->insertOne($data);
        $id = $res->getInsertedId();
        return (array) $this->collection->findOne(['_id' => $id]);
    }

    /**
     * Load a form entry from a MongoDB collection
     *
     * @param string $id Document id
     * @return array
     * @throws MongoDB\Exception\UnsupportedException
     * @throws MongoDB\Exception\InvalidArgumentException
     * @throws MongoDB\Driver\Exception\RuntimeException
     */
    public function getEntry($id)
    {
        $data = (array) $this->collection->findOne(['_id' => new ObjectID($id)]);
        $data['id'] = (string) $data['_id'];
        unset($data['_id']);
        return $data;
    }
}
