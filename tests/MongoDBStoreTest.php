<?php
namespace BZContact;

use PHPUnit\Framework\TestCase;

class MongoDBStoreTest extends TestCase
{
    public function testNewStore()
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $logger->method('info')->willReturn(true);

        $document = $this->createMock(\MongoDB\InsertOneResult::class);
        $document->method('getInsertedID')->willReturn(new \MongoDB\BSON\ObjectID);

        $collection = $this->createMock(\MongoDB\Collection::class);
        $collection->method('insertOne')->willReturn($document);

        $store = new Form\Store\MongoDBStore($collection, $logger);
        $this->assertInstanceOf(Form\Store\MongoDBStore::class, $store);

        // Empty data should not be saved
        $entry = $store->createEntry([]);
        $this->assertInstanceOf(Form\Store\FormEntryInterface::class, $entry);
        $this->assertFalse($entry->save());

        // Mock entry creation
        $collection->method('findOne')->willReturn(new \ArrayObject(['foo' => 'bar', '_id' => 'abc']));
        $entry = $store->createEntry(['foo' => 'bar']);
        $this->assertInstanceOf(Form\Store\FormEntryInterface::class, $entry);
        $this->assertInternalType('array', $entry->save());

        $id = new \MongoDB\BSON\ObjectID;
        $collection->method('findOne')->willReturn(new \ArrayObject(['_id' => $id]));
        $entry = $store->getEntry((string) $id);
        $this->assertInternalType('array', $entry);
    }
}
