<?php

namespace App\General\Controller;

use Core\Controller\Controller;
use Core\Controller\ControllerInterface;

class ErrorController extends Controller implements ControllerInterface
{
    /**
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function notAdmin()
    {
        $this->render('error/notAdmin.twig', []);
    }
}
