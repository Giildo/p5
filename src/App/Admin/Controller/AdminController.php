<?php

namespace App\Admin\Controller;

use App\Admin\Model\UserModel;
use Core\Controller\Controller;
use Core\Controller\ControllerInterface;

class AdminController extends Controller implements ControllerInterface
{
    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index()
    {
        $id = $_SESSION['user']['id'];

        $user = $this->userModel->userByAdmin($id);

        $this->render('admin/index.twig', compact('user'));
    }
}
