<?php

namespace BZContact\Form\Store;

use Psr\Log\LoggerInterface;

class FormEntry implements FormEntryInterface
{
    protected $logger = null;
    protected $data = [];

    /**
     * Construct a Basic form entry
     *
     * @param array $data Array of user data
     * @param LoggerInterface $logger The logger object
     * @return void
     */
    public function __construct(array $data, LoggerInterface $logger)
    {
        $this->logger = $logger;
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
        $this->logger->info("Saving...", ['data' => $this->data]);
        return true;
    }
}
