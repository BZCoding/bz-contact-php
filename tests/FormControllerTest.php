<?php
namespace BZContact;

use PHPUnit\Framework\TestCase;

class FormControllerTest extends TestCase
{
    public function setUp()
    {
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->logger->method('info')->willReturn(true);

        $this->renderer = $this->createMock(\Slim\Views\PhpRenderer::class);

        $this->form = $this->createMock(\BZContact\Form\FormBuilder::class);

        $this->validator = $this->createMock(\BZContact\Form\Validator::class);

        $this->entry = $this->createMock(\BZContact\Form\Store\FormEntry::class);
        $this->entry->method('save')->willReturn(['foo' => 'bar']);

        $this->store = $this->createMock(\BZContact\Form\Store\DummyStore::class);
        $this->store->method('createEntry')->willReturn($this->entry);

        $this->dispatcher = $this->createMock(\Symfony\Component\EventDispatcher\EventDispatcher::class);

        $this->container = $this->createMock(\Interop\Container\ContainerInterface::class);
        $this->dependencies = [
            ['settings', [
                'redirect_thankyou' => null
            ]],
            ['logger', $this->logger],
            ['renderer', $this->renderer],
            ['form', $this->form],
            ['validator', $this->validator],
            ['store', $this->store],
            ['dispatcher', $this->dispatcher]
        ];
        $this->container->method('get')->will($this->returnValueMap($this->dependencies));

        $this->request = $this->createMock(\Slim\Http\Request::class);
        $this->request->method('getParsedBody')->willReturn(['foo' => 'bar']);

        $this->response = $this->createMock(\Slim\Http\Response::class);
    }

    public function testFormWithValidData()
    {
        $controller = new Controller\FormController($this->container);
        $this->form->method('validates')->willReturn(true);
        $this->renderer->method('render')->willReturn('ThankYou.html');
        $result = $controller($this->request, $this->response, []);
        $this->assertEquals('ThankYou.html', $result);
    }

    public function testFormWithInvalidData()
    {
        $controller = new Controller\FormController($this->container);
        $this->form->method('validates')->willReturn(false);
        $this->form->method('setErrorStore')->willReturn(true);
        $this->validator->method('errors')->willReturn(['foo' => 'bar']);
        $this->renderer->method('render')->willReturn('Errors');
        $result = $controller($this->request, $this->response, []);
        $this->assertEquals('Errors', $result);
    }

    public function testFormWithValidDataAndThankyouPage()
    {
        // We need to recreate the container, or it won't take new settings
        $thankyou = 'http://example.com/thankyou.html';
        $this->dependencies[0][1]['redirect_thankyou'] = $thankyou;
        $this->container = $this->createMock(\Interop\Container\ContainerInterface::class);
        $this->container->method('get')->will($this->returnValueMap($this->dependencies));

        $controller = new Controller\FormController($this->container);
        $this->form->method('validates')->willReturn(true);
        $this->response->method('withHeader')->willReturn($thankyou);
        $this->response->method('withStatus')->willReturn($this->response);
        $result = $controller($this->request, $this->response, []);
        $this->assertEquals($thankyou, $result);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Unable to save POSTed data
     */
    public function testFormWithErrorOnSave()
    {
        // We need to recreate the new deps and the container, or it won't take new settings
        $this->entry = $this->createMock(\BZContact\Form\Store\FormEntry::class);
        $this->entry->method('save')->willReturn(false);

        $this->store = $this->createMock(\BZContact\Form\Store\DummyStore::class);
        $this->store->method('createEntry')->willReturn($this->entry);
        $this->dependencies[5] = ['store', $this->store];

        $this->container = $this->createMock(\Interop\Container\ContainerInterface::class);
        $this->container->method('get')->will($this->returnValueMap($this->dependencies));

        $controller = new Controller\FormController($this->container);
        $this->form->method('validates')->willReturn(true);
        $this->renderer->method('render')->willReturn('ThankYou.html');
        $result = $controller($this->request, $this->response, []);
        $this->expectException(\Exception::class);
    }
}
