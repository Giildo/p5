<?php

namespace App\Admin\Controller;

use Core\Controller\Controller;
use Core\Controller\ControllerInterface;

class AdminController extends Controller implements ControllerInterface
{
    /**
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index()
    {
        $this->render('admin/index.twig', []);
    }
}
