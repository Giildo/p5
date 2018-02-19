<?php

namespace App\Admin\Controller;

use App\Blog\Model\PostModel;
use Core\Controller\Controller;
use Core\Controller\ControllerInterface;

class PostController extends Controller implements ControllerInterface
{
    /**
     * @var PostModel
     */
    protected $postModel;

    /**
     * @param array $vars
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index(array $vars): void
    {
        $admin = false;

        if ($_SESSION['user']['idAdmin'] === '1') {
            $admin = true;
            $nbPage = $this->postModel->count();
        } else {
            $nbPage = $this->postModel->count('user', $_SESSION['user']['id']);
        }

        $paginationOptions = $this->pagination($vars, $nbPage, 'admin.limit.post');

        $posts = $this->postModel->findAllByUserAndCategory(
            $_SESSION['user']['id'],
            $paginationOptions['start'],
            $paginationOptions['limit'],
            true,
            'ORDER BY updatedAt DESC',
            $admin
        );

        $this->render('admin/posts/index.twig', compact('posts', 'paginationOptions'));
    }
}
