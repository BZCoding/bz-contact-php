<?php

namespace BZContact\Form\Store;

use Psr\Log\LoggerInterface;

class DummyStore implements StoreInterface
{
    protected $logger = null;

    /**
     * Construct a Dummy Store
     *
     * @param LoggerInterface $logger The logger object
     * @return void
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Create a new form entry with given form data
     *
     * @param array $data Array of user data
     * @return FormEntryInterface
     */
    public function createEntry(array $data)
    {
        $this->logger->info("Received", ['data' => $data]);
        return new FormEntry($data, $this->logger);
    }
}
