<?php
namespace BZContact;

use PHPUnit\Framework\TestCase;

class OldInputProviderTest extends TestCase
{
    public function testEmptyProvider()
    {
        $p = new Form\OldInputProvider([]);
        $this->assertFalse($p->hasOldInput());
        $this->assertNull($p->getOldInput('foo'));
    }

    public function testFullProvider()
    {
        $p = new Form\OldInputProvider(['foo' => 'bar']);
        $this->assertTrue($p->hasOldInput());
        $this->assertEquals('bar', $p->getOldInput('foo'));
        $this->assertNull($p->getOldInput('bar'));
    }
}
