<?php
namespace BZContact;

use PHPUnit\Framework\TestCase;

class PagesControllerTest extends TestCase
{
    public function setUp()
    {
        $this->renderer = $this->createMock(\Slim\Views\PhpRenderer::class);
        $this->renderer->method('render')->willReturn('Hello World');

        $this->form = $this->createMock(\BZContact\Form\FormBuilder::class);

        $this->container = $this->createMock(\Interop\Container\ContainerInterface::class);
        $this->container->method('get')->will($this->returnValueMap([
            ['renderer', $this->renderer],
            ['form', $this->form]
        ]));

        $this->request = $this->createMock(\Slim\Http\Request::class);
        $this->response = $this->createMock(\Slim\Http\Response::class);
    }

    public function testTheHomePage()
    {
        $pages = new Controller\PagesController($this->container);
        $page = $pages->index($this->request, $this->response);
        $this->assertEquals('Hello World', $page);
    }

    public function testThePrivacyPage()
    {
        $pages = new Controller\PagesController($this->container);
        $page = $pages->privacy($this->request, $this->response);
        $this->assertEquals('Hello World', $page);
    }

    public function testTheTermsPage()
    {
        $pages = new Controller\PagesController($this->container);
        $page = $pages->terms($this->request, $this->response);
        $this->assertEquals('Hello World', $page);
    }
}
