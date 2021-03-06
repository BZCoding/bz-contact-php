<?php
namespace BZContact;

use PHPUnit\Framework\TestCase;

class DummyStoreTest extends TestCase
{
    public function testNewStore()
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $logger->expects($this->any())->method('info')->willReturn(true);

        $store = new Form\Store\DummyStore($logger);
        $this->assertInstanceOf(Form\Store\DummyStore::class, $store);

        $entry = $store->createEntry([]);
        $this->assertInstanceOf(Form\Store\FormEntryInterface::class, $entry);

        // Empty data should not be saved
        $this->assertFalse($entry->save());

        $this->assertInternalType('array', $entry->getData());

        $entry = $store->getEntry('abc');
        $this->assertInternalType('array', $entry);
    }
}
