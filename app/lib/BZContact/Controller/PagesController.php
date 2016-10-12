<?php
namespace BZContact\Controller;

use Interop\Container\ContainerInterface;

class PagesController
{
    protected $ci;
    protected $view;
    protected $form;

    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
        $this->view = $this->ci->get('renderer');
        $this->form = $this->ci->get('form');
    }

    public function index($request, $response, $args)
    {
        return $this->view->render($response, 'index.phtml', [
            'form' => $this->form
        ]);
    }

    public function privacy($request, $response, $args)
    {
        return $this->view->render($response, 'privacy.phtml', [
            'pageTitle' => 'Privacy & Cookies'
        ]);
    }

    public function terms($request, $response, $args)
    {
        return $this->view->render($response, 'terms.phtml', [
            'pageTitle' => 'Terms of Service'
        ]);
    }
}
