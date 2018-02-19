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
        $nbPage = $this->postModel->count();

        $paginationOptions = $this->pagination($vars, $nbPage, 'admin.limit.post');

        $posts = $this->postModel->findAll(
            $paginationOptions['start'],
            $paginationOptions['limit'],
            true,
            ' ORDER BY updatedAt DESC '
        );

        $this->render('admin/posts/index.twig', compact('posts', 'paginationOptions'));
    }
}
