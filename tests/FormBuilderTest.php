<?php
namespace BZContact;

use PHPUnit\Framework\TestCase;

class FormBuilderTest extends TestCase
{

    /**
     * Raise an error when no data is passed to constructor
     * @expectedException PHPUnit_Framework_Error
     */
    public function testErrorOnNoData()
    {
        $builder = new Form\FormBuilder();
    }

    /**
     * Raise an error when invalid JSON is passed to constructor
     * @expectedExceptionMessage Invalid JSON data
     */
    public function testErrorOnInvalidJson()
    {
        $this->expectException(\ErrorException::class);
        $builder = new Form\FormBuilder('{"fields":[{}{}]}');
    }

    /**
     * Raise an error when an empty JSON object is passed to constructor
     * @expectedExceptionMessage Invalid form data
     */
    public function testErrorOnEmptyJson()
    {
        $this->expectException(\ErrorException::class);
        $builder = new Form\FormBuilder('{}');
    }

    /**
     * Basic valid form
     */
    public function testABasicValidForm()
    {
        $data = '{"fields":[';
        $data .= '{"name":"name","label":"Your name","placeholder":"i.e. John Doe"},';
        $data .= '{"id":"contact-email","name":"email","type":"email","required":true},';
        $data .= '{"id":"contact-phone","name":"contact-phone","type":"tel","class":"phone"},';
        $data .= '{"id":"contact-message","name":"contact-message","type":"textarea","rows":8,"cols":20},';
        $data .= '{"id":"referral","type":"select",'
            .'"label": "Choose one","options": [{"f": "Foo"},{"c": "Bar"},"Baz"]},';
        $data .= '{"id":"terms","name":"terms","type":"checkbox","value":"agree"},';
        $data .= '{"id":"contact-submit","name":"contact-submit","type":"submit","value":"Send"}';
        $data .= ']}';
        $builder = new Form\FormBuilder($data);

        $nameFieldMarkup = $builder->field('field-1')->__toString();

        // Test that ID are auto generated
        $this->assertContains('id="field-1"', $nameFieldMarkup);

        $this->assertContains('name="name"', $nameFieldMarkup);

        // Test default field type is text
        $this->assertContains('type="text"', $nameFieldMarkup);

        // Test placeholder attribute
        $this->assertContains('placeholder="i.e. John Doe"', $nameFieldMarkup);

        // Test label generation
        $labelForNameMarkup = $builder->label()->forId('field-1')->__toString();
        $this->assertContains('<label for="field-1">Your name</label>', $labelForNameMarkup);

        // Test default required is false
        $this->assertThat(
            $nameFieldMarkup,
            $this->logicalNot(
                $this->stringContains('required="required"', $nameFieldMarkup)
            )
        );

        $emailFieldMarkup = $builder->field('contact-email')->__toString();

        // Test that custom ID are generated generated
        $this->assertContains('id="contact-email"', $emailFieldMarkup);

        $this->assertContains('name="email"', $emailFieldMarkup);

        // Test custom field type is email
        $this->assertContains('type="email"', $emailFieldMarkup);

        // Test custom required is applied
        $this->assertContains('required="required"', $emailFieldMarkup);

        // Test generated markup, for custom field type
        $phoneFieldMarkup = $builder->field('contact-phone')->__toString();

        // Test custom field type is tel
        $this->assertContains('type="tel"', $phoneFieldMarkup);

        // Test additional CSS class
        $this->assertContains('class="phone"', $phoneFieldMarkup);

        // Test custom field tel directly
        $phoneFieldMarkup = $builder->tel('phone')->__toString();
        $this->assertContains('type="tel" name="phone"', $phoneFieldMarkup);

        // Test textarea
        $messageFieldMarkup = $builder->field('contact-message')->__toString();
        $this->assertContains(
            '<textarea name="contact-message" rows="8" cols="20" id="contact-message">',
            $messageFieldMarkup
        );

        // Test checkbox field
        $checkboxFieldMarkup = $builder->field('terms')->__toString();
        $this->assertContains('type="checkbox" name="terms" value="agree" id="terms"', $checkboxFieldMarkup);

        // Test select field
        // Also test that on empty name attribute, ID is used
        $selectFieldMarkup = $builder->field('referral')->__toString();
        $this->assertContains('<select name="referral" id="referral">', $selectFieldMarkup);
        $this->assertContains('<option value="f">Foo</option>', $selectFieldMarkup);
        $this->assertContains('<option value="c">Bar</option>', $selectFieldMarkup);
        $this->assertContains('<option value="0">Baz</option>', $selectFieldMarkup);

        // Test submit field
        $submitFieldMarkup = $builder->field('contact-submit')->__toString();
        $this->assertContains('type="submit" name="contact-submit" id="contact-submit">Send', $submitFieldMarkup);

        // Test that an empty form element is returned when an unknown id is passed
        $emptyFieldMarkup = $builder->field('unknown');
        $this->assertInstanceOf(Form\Elements\EmptyElement::class, $emptyFieldMarkup);
        $this->assertEmpty($emptyFieldMarkup->__toString());
    }

    /**
     * Test form attributes
     */
    public function testFormAttributes()
    {
        $data = '{"attributes":{';
        $data .= '"id":"my-form",';
        $data .= '"class":"test-form",';
        $data .= '"method":"put",';
        $data .= '"action":"/test"';
        $data .= '},';
        $data .= '"fields":[';
        $data .= '{"id":"contact-email","name":"email","type":"email","required":true}';
        $data .= ']}';
        $builder = new Form\FormBuilder($data);

        // Test open form attributes
        $openFormMarkup = $builder->open()->addClass('foo')->__toString();
        $this->assertContains('id="my-form"', $openFormMarkup);
        $this->assertContains('class="test-form foo"', $openFormMarkup);
        $this->assertContains('method="POST"', $openFormMarkup);
        $this->assertContains('<input type="hidden" name="_method" value="PUT">', $openFormMarkup);
        $this->assertContains('action="/test"', $openFormMarkup);

        $emailFieldMarkup = $builder->field('contact-email')->__toString();
        $this->assertContains('id="contact-email"', $emailFieldMarkup);
        $this->assertContains('name="email"', $emailFieldMarkup);
        $this->assertContains('type="email"', $emailFieldMarkup);
        $this->assertContains('required="required"', $emailFieldMarkup);
    }

    /**
     * Test multipart encoding
     */
    public function testMultipartEncoding()
    {
        $data = '{"attributes":{';
        $data .= '"encoding":"multipart"';
        $data .= '},';
        $data .= '"fields":[';
        $data .= '{"id":"contact-email","name":"email","type":"email","required":true}';
        $data .= ']}';
        $builder = new Form\FormBuilder($data);
        $openFormMarkup = $builder->open()->addClass('foo')->__toString();
        $this->assertContains('method="POST"', $openFormMarkup);
        $this->assertContains('enctype="multipart/form-data"', $openFormMarkup);
    }

    /**
     * Test custom encoding
     */
    public function testCustomEncoding()
    {
        $data = '{"attributes":{';
        $data .= '"encoding":"my-custom/encoding"';
        $data .= '},';
        $data .= '"fields":[';
        $data .= '{"id":"contact-email","name":"email","type":"email","required":true}';
        $data .= ']}';
        $builder = new Form\FormBuilder($data);
        $openFormMarkup = $builder->open()->addClass('foo')->__toString();
        $this->assertContains('method="POST"', $openFormMarkup);
        $this->assertContains('enctype="my-custom/encoding"', $openFormMarkup);
    }

    public function testRadioButtonGroup()
    {
        $data = '{"fields":[';
        $data .= '{"id":"myradio","name":"myradio","label":"My Radio", "type":"radio", "required":true,';
        $data .= '"options":["foo", "bar"]},';
        $data .= '{"id":"contact-submit","name":"contact-submit","type":"submit","value":"Send"}';
        $data .= ']}';
        $builder = new Form\FormBuilder($data);

        $radioFieldMarkup = $builder->field('myradio')->__toString();
        $this->assertContains('<fieldset id="myradio">', $radioFieldMarkup);
        $this->assertContains(
            '<input type="radio" name="myradio" value="foo" id="myradio-foo" required="required"',
            $radioFieldMarkup
        );
        $this->assertContains(
            '<input type="radio" name="myradio" value="bar" id="myradio-bar" required="required"',
            $radioFieldMarkup
        );
        $this->assertContains('</fieldset>', $radioFieldMarkup);
    }

    /**
     * Raise an error when the form has no fields
     * @expectedExceptionMessage Invalid form data: no fields
     */
    public function testErrorOnNoFields()
    {
        $data = '{"attributes":{';
        $data .= '},';
        $data .= '"fields":[';
        $data .= ']}';
        $this->expectException(\ErrorException::class);
        $builder = new Form\FormBuilder($data);
    }

    /**
     * Raise an error on duplicate ID attributes
     * @expectedExceptionMessage Attribute 'id' must be unique, field 3
     */
    public function testErrorOnDuplicateId()
    {
        $data = '{"fields":[';
        $data .= '{"id":"foo","name":"foo"},';
        $data .= '{"id":"bar","name":"bar"}';
        $data .= '{"id":"foo","name":"baz"}';
        $data .= ']}';
        $this->expectException(\ErrorException::class);
        $builder = new Form\FormBuilder($data);
    }

    /**
     * Raise an error on invalid method
     * @expectedExceptionMessage Invalid form method: foo
     */
    public function testErrorOnInvalidMethod()
    {
        $data = '{"attributes":{';
        $data .= '"method":"foo"';
        $data .= '},';
        $data .= '"fields":[';
        $data .= '{"id":"contact-email","name":"email","type":"email","required":true}';
        $data .= ']}';
        $this->expectException(\ErrorException::class);
        $builder = new Form\FormBuilder($data);
        $openFormMarkup = $builder->open()->addClass('foo')->__toString();
    }

    /**
     * Test builder validation with mock validator
     */
    public function testValidate()
    {
        $data = '{"fields":[';
        $data .= '{"name":"name","label":"Your name","placeholder":"i.e. John Doe"},';
        $data .= '{"id":"contact-email","name":"email","type":"email","required":true},';
        $data .= '{"id":"contact-submit","name":"contact-submit","type":"submit","value":"Send"}';
        $data .= ']}';

        $builder = new Form\FormBuilder($data);

        // Test with a validator that returns True
        $validator = $this->createMock(Form\Validator::class);
        $validator->expects($this->any())->method('validate')->willReturn(true);
        $this->assertTrue($builder->validates([], $validator));

        // Test with a validator that returns False
        $validator = $this->createMock(Form\Validator::class);
        $validator->expects($this->any())->method('validate')->willReturn(false);
        $this->assertFalse($builder->validates([], $validator));
    }

    /**
     * Test validation error retrieval
     */
    public function testFieldError()
    {
        $data = '{"fields":[';
        $data .= '{"id":"contact-name","name":"name","label":"Your name","placeholder":"i.e. John Doe"},';
        $data .= '{"id":"contact-email","name":"email","type":"email","required":true},';
        $data .= '{"id":"contact-submit","name":"contact-submit","type":"submit","value":"Send"}';
        $data .= ']}';

        $builder = new Form\FormBuilder($data);

        // Test error for non existing field (should be empty)
        $errorStore = $this->createMock(Form\ErrorStore::class);
        $errorStore->expects($this->any())->method('hasError')->willReturn(false);
        $errorStore->expects($this->any())->method('getError')->willReturn(null);
        $builder->setErrorStore($errorStore);
        $this->assertInternalType('string', $builder->getError('unknown'));
        $this->assertEmpty($builder->getError('unknown'));

        // Test error for existing field, should be formatted HTML
        $errorStore = $this->createMock(Form\ErrorStore::class);
        $errorStore->expects($this->any())->method('hasError')->with('name')->willReturn(true);
        $errorStore->expects($this->any())->method('getError')->with('name')->willReturn('Name is required');
        $builder->setErrorStore($errorStore);
        $this->assertInternalType('string', $builder->getError('name'));
        $this->assertEquals('<span class="error">Name is required</span>', $builder->getError('contact-name'));
    }

    /**
     * Test CSRF field generation
     */
    public function testCsrf()
    {
        $data = '{"fields":[';
        $data .= '{"id":"contact-name","name":"name","label":"Your name","placeholder":"i.e. John Doe"},';
        $data .= '{"id":"contact-email","name":"email","type":"email","required":true},';
        $data .= '{"id":"contact-submit","name":"contact-submit","type":"submit","value":"Send"}';
        $data .= ']}';

        $builder = new Form\FormBuilder($data);
        $builder->setToken([
            'csrf_name' => 'foo',
            'csrf_value' => 'bar'
        ]);
        $openFormMarkup = $builder->open()->__toString();
        $this->assertContains('<input type="hidden" name="csrf_name" value="foo">', $openFormMarkup);
        $this->assertContains('<input type="hidden" name="csrf_value" value="bar">', $openFormMarkup);
    }
}
