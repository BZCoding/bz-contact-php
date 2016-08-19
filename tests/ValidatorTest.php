<?php
namespace BZContact;

use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testValidForm()
    {
        // Create validator
        $v = new Form\Validator();

        // Add rules
        $rules = [
            'required' => [
                ['field' => 'bar', 'message' => 'Bar is required']
            ]
        ];
        $v->rules($rules);

        // Add single rule
        // NB: This rule is not inserted because internal validator is not set,
        //     it is set by validate()
        $v->rule('required', 'foo');

        // Add labels
        $v->labels([]);

        // Test valid form
        $this->assertTrue($v->validate(['foo' => '123', 'bar' => '567']));

        // This is also valid because the single rule is not inserted
        $this->assertTrue($v->validate(['bar' => '567']));

        $this->assertInternalType('array', $v->errors());
    }

    public function testInvalidForm()
    {
        // Create validator
        $v = new Form\Validator();

        // Add rules
        $rules = [
            'required' => [
                ['field' => 'foo'],
                ['field' => 'bar', 'message' => 'Bar is required']
            ]
        ];
        $v->rules($rules);

        // This should be empty because internal validator is not set
        $this->assertInternalType('array', $v->errors());

        // This should be invalid
        $this->assertFalse($v->validate(['foo' => '567']));

        // Test errors and error messages
        $errors = $v->errors();
        $this->assertInternalType('array', $errors);
        $this->assertArrayHasKey('bar', $errors);
        $this->assertEquals($rules['required'][1]['message'], $errors['bar'][0]);
    }
}
