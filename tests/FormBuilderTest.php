<?php
use PHPUnit\Framework\TestCase;

class FormBuilderTest extends TestCase
{

    /**
     * Raise an error when no data is passed to constructor
     * @expectedException PHPUnit_Framework_Error
     */
    public function testErrorOnNoData()
    {
        $builder = new BZContact\Form\FormBuilder();
    }

    /**
     * Raise an error when invalid JSON is passed to constructor
     * @expectedExceptionMessage Invalid JSON data
     */
    public function testErrorOnInvalidJson()
    {
        $this->expectException(ErrorException::class);
        $builder = new BZContact\Form\FormBuilder('{"fields":[{}{}]}');
    }

    /**
     * Raise an error when an empty JSON object is passed to constructor
     * @expectedExceptionMessage Invalid form data
     */
    public function testErrorOnEmptyJson()
    {
        $this->expectException(ErrorException::class);
        $builder = new BZContact\Form\FormBuilder('{}');
    }

    /**
     * Basic valid form
     */
    public function testABasicValidForm()
    {
        $data = '{"fields":[';
        $data .= '{"name":"name","label": "Your name"},';
        $data .= '{"id":"contact-email","name":"email","type":"email","required":true},';
        $data .= '{"id":"contact-phone","name":"contact-phone","type":"tel"},';
        $data .= '{"id":"contact-message","name":"contact-message","type":"textarea","rows":8,"cols":20},';
        $data .= '{"id":"referral","name":"referral","type":"select","label": "Choose one","options": [{"f": "Foo"},{"c": "Bar"},"Baz"]},';
        $data .= '{"id":"terms","name":"terms","type":"checkbox","value":"agree"},';
        $data .= '{"id":"contact-submit","name":"contact-submit","type":"submit","value":"Send"}';
        $data .= ']}';
        $builder = new BZContact\Form\FormBuilder($data);

        $nameFieldMarkup = $builder->field('field-1')->__toString();

        // Test that ID are auto generated
        $this->assertContains('id="field-1"', $nameFieldMarkup);

        $this->assertContains('name="name"', $nameFieldMarkup);

        // Test default field type is text
        $this->assertContains('type="text"', $nameFieldMarkup);

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

        // Test custom field tel directly
        $phoneFieldMarkup = $builder->tel('phone')->__toString();
        $this->assertContains('type="tel" name="phone"', $phoneFieldMarkup);

        // Test textarea
        $messageFieldMarkup = $builder->field('contact-message')->__toString();
        $this->assertContains('<textarea name="contact-message" rows="8" cols="20" id="contact-message">', $messageFieldMarkup);

        // Test checkbox field
        $checkboxFieldMarkup = $builder->field('terms')->__toString();
        $this->assertContains('type="checkbox" name="terms" value="agree" id="terms"', $checkboxFieldMarkup);

        // Test select field
        $selectFieldMarkup = $builder->field('referral')->__toString();
        $this->assertContains('<select name="referral" id="referral">', $selectFieldMarkup);
        $this->assertContains('<option value="f">Foo</option>', $selectFieldMarkup);
        $this->assertContains('<option value="c">Bar</option>', $selectFieldMarkup);
        $this->assertContains('<option value="0">Baz</option>', $selectFieldMarkup);

        // Test submit field
        $submitFieldMarkup = $builder->field('contact-submit')->__toString();
        $this->assertContains('type="button" name="contact-submit" id="contact-submit">Send', $submitFieldMarkup);
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
        $builder = new BZContact\Form\FormBuilder($data);

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
        $builder = new BZContact\Form\FormBuilder($data);
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
        $builder = new BZContact\Form\FormBuilder($data);
        $openFormMarkup = $builder->open()->addClass('foo')->__toString();
        $this->assertContains('method="POST"', $openFormMarkup);
        $this->assertContains('enctype="my-custom/encoding"', $openFormMarkup);
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
        $this->expectException(ErrorException::class);
        $builder = new BZContact\Form\FormBuilder($data);
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
        $this->expectException(ErrorException::class);
        $builder = new BZContact\Form\FormBuilder($data);
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
        $this->expectException(ErrorException::class);
        $builder = new BZContact\Form\FormBuilder($data);
        $openFormMarkup = $builder->open()->addClass('foo')->__toString();
    }
}
