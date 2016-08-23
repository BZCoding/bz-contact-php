<?php
namespace BZContact;

use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }

    public function testValidates()
    {
        $form = new Form\Form;

        // Test with a validator that returns True
        $validator = $this->createMock(Form\Validator::class);
        $validator->expects($this->any())->method('validate')->willReturn(true);
        $this->assertTrue($form->validates([], $validator));

        // Test with a validator that returns False
        $validator = $this->createMock(Form\Validator::class);
        $validator->expects($this->any())->method('validate')->willReturn(false);
        $this->assertFalse($form->validates([], $validator));
    }

    public function testParseSelectOptions()
    {
        $form = new Form\Form;

        // Test empty options
        $this->assertEmpty($form->parseSelectOptions());
        $this->assertInternalType('array', $form->parseSelectOptions());

        // Test simple array of options
        $options = ['Foo', 'Bar', 'Baz'];
        $this->assertEquals($options, $form->parseSelectOptions($options));

        // Test array of object options
        $foo = new \stdClass;
        $foo->a = 'Foo';
        $bar = new \stdClass;
        $bar->b = 'Bar';
        $bar->c = 'Baz';
        $options = [$foo, $bar];
        $this->assertEquals(
            [
                'a' => 'Foo',
                'b' => 'Bar'
            ],
            $form->parseSelectOptions($options)
        );
    }

    public function testBuildRules()
    {
        $form = new Form\Form;

        // Standard required field
        $text1 = new \stdClass;
        $text1->type = 'text';
        $text1->name = 'foo';
        $text1->required = true;
        $form->fields[] = $text1;

        // Required field with custom error message
        $text2 = new \stdClass;
        $text2->type = 'text';
        $text2->required = true;
        $text2->name = 'bar';
        $text2->error = 'Bar is a required field';
        $form->fields[] = $text2;

        // Email field
        $email = new \stdClass;
        $email->type = 'email';
        $email->required = true;
        $email->name = 'email';
        $form->fields[] = $email;

        // Required checkbox field
        $checkbox = new \stdClass;
        $checkbox->type = 'checkbox';
        $checkbox->required = true;
        $checkbox->name = 'terms';
        $form->fields[] = $checkbox;

        // Select field
        $select = new \stdClass;
        $select->type = 'select';
        $select->required = false;
        $select->name = 'options';
        $select->options = ['one', 'two', 'three'];
        $form->fields[] = $select;

        $rules = $this->invokeMethod($form, 'buildRules');

        // Assert rules are built
        $this->assertInternalType('array', $rules);

        // Check standard required field
        $this->assertArrayHasKey('required', $rules);
        $this->assertEquals('foo', $rules['required'][0]['field']);
        $this->assertEquals('{field} cannot be empty', $rules['required'][0]['message']);

        // Check custom error message on required field
        $this->assertEquals('bar', $rules['required'][1]['field']);
        $this->assertEquals($text2->error, $rules['required'][1]['message']);

        // Check email field
        $this->assertArrayHasKey('email', $rules);
        $this->assertEquals('email', $rules['required'][2]['field']);
        $this->assertEquals('email', $rules['email'][0]['field']);

        // Check the accepted rule on checkbox
        $this->assertArrayHasKey('accepted', $rules);
        $this->assertEquals('terms', $rules['accepted'][0]['field']);

        // Check the IN rule for select fields
        $this->assertArrayHasKey('in', $rules);
        $this->assertEquals('options', $rules['in'][0]['field']);
    }

    public function testBuildLabels()
    {
        $form = new Form\Form;

        $field = new \stdClass;
        $field->type = 'text';
        $field->name = 'name';
        $field->label = 'Your Name';
        $field->required = true;
        $form->fields[] = $field;

        $labels = $this->invokeMethod($form, 'buildLabels');
        $this->assertInternalType('array', $labels);
    }

    public function testFilter()
    {
        $form = new Form\Form;

        // Standard required field
        $text1 = new \stdClass;
        $text1->type = 'text';
        $text1->name = 'foo';
        $text1->id = 'foo';
        $text1->required = true;
        $form->fields[$text1->id] = $text1;
        $form->names[$text1->name] = $text1->id;

        // Required field with custom error message
        $text2 = new \stdClass;
        $text2->type = 'text';
        $text2->required = true;
        $text2->name = 'bar';
        $text2->id = 'bar';
        $text2->error = 'Bar is a required field';
        $form->fields[$text2->id] = $text2;
        $form->names[$text2->name] = $text2->id;

        $submit = new \stdClass;
        $submit->type = 'submit';
        $submit->required = false;
        $submit->name = 'saveForm';
        $submit->id = 'save-form';
        $submit->value = 'Send';
        $submit->save = false;
        $form->fields[$submit->id] = $submit;
        $form->names[$submit->name] = $submit->id;

        $data = [
            'foo' => 'Foo Value',
            'bar' => 'Bar Value',
            'baz' => 'Shouldnt be included',
            'saveForm' => ''
        ];

        $filteredData = $form->filter($data);
        $this->assertInternalType('array', $filteredData);
        $this->assertArrayNotHasKey('baz', $filteredData);
        $this->assertArrayNotHasKey('saveForm', $filteredData);
    }
}
