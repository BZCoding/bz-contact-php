<?php
namespace BZContact;

use PHPUnit\Framework\TestCase;

class ErrorStoreTest extends TestCase
{
    protected $errors = [
        'name' => ['Name is a required field'],
        'email' => [
            'Email is not valid',
            'Email is a required field'
        ]
    ];

    public function testGetError()
    {
        $store = new Form\ErrorStore($this->errors);

        $this->assertTrue($store->hasError('name'));
        $this->assertTrue($store->hasError('email'));
        $this->assertFalse($store->hasError('address'));

        $this->assertEquals($this->errors['name'][0], $store->getError('name'));
        $this->assertEquals($this->errors['email'][0], $store->getError('email'));
        $this->assertEquals(null, $store->getError('address'));
    }
}
