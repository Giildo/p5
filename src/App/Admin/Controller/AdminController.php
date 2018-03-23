<?php

namespace App\Admin\Controller;

use App\Admin\Model\UserModel;
use App\Controller\AppController;
use Core\Controller\ControllerInterface;

class AdminController extends AppController implements ControllerInterface
{
    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index()
    {
        $user = $this->findUserConnected();

        $this->render('admin/index.twig', compact('user'));
    }
}
