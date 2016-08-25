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
        $this->assertTrue($entry->save());
        $this->assertInternalType('array', $entry->getData());
    }
}
