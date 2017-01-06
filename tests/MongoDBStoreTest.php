<?php
namespace BZContact;

use PHPUnit\Framework\TestCase;

class MongoDBStoreTest extends TestCase
{
    public function testNewStore()
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $logger->method('info')->willReturn(true);

        $document = $this->createMock(\Sokil\Mongo\Document::class);
        $document->method('save')->willReturn($document);

        $collection = $this->createMock(\Sokil\Mongo\Collection::class);
        $collection->method('createDocument')->willReturn($document);

        $store = new Form\Store\MongoDBStore($collection, $logger);
        $this->assertInstanceOf(Form\Store\MongoDBStore::class, $store);

        // Empty data should not be saved
        $entry = $store->createEntry([]);
        $this->assertInstanceOf(Form\Store\FormEntryInterface::class, $entry);
        $this->assertFalse($entry->save());

        // Mock entry creation
        $document->method('toArray')->willReturn(['foo' => 'bar', '_id' => 'abc']);
        $entry = $store->createEntry(['foo' => 'bar']);
        $this->assertInstanceOf(Form\Store\FormEntryInterface::class, $entry);
        $this->assertInternalType('array', $entry->save());

        $document = new \stdClass();
        $document_id = new \stdClass();
        $document_id->{'$id'} = 'abc';
        $document->_id = $document_id;
        $collection->method('getDocument')->willReturn($document);
        $entry = $store->getEntry('abc');
        $this->assertInternalType('array', $entry);
    }
}
