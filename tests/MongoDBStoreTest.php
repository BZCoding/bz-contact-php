<?php
namespace BZContact;

use PHPUnit\Framework\TestCase;

class MongoDBStoreTest extends TestCase
{
    public function testNewStore()
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $logger->expects($this->any())->method('info')->willReturn(true);

        $collection = $this->createMock(\Sokil\Mongo\Collection::class);
        $collection->expects($this->any())->method('insert')->willReturn(true);

        $store = new Form\Store\MongoDBStore($collection, $logger);
        $this->assertInstanceOf(Form\Store\MongoDBStore::class, $store);

        $entry = $store->createEntry([]);
        $this->assertInstanceOf(Form\Store\FormEntryInterface::class, $entry);
        $this->assertTrue($entry->save());
    }
}
