<?php

namespace App\Blog\Controller;

use App\Blog\Model\CategoryModel;
use App\Blog\Model\PostModel;
use Core\Controller\Controller;
use Core\Controller\ControllerInterface;
use Core\Controller\InstantiationModels;
use Psr\Container\ContainerInterface;
use Twig_Environment;

/**
 * Gère l'affichage des Posts
 *
 * Class BlogController
 * @package App\Blog\Controller
 */
class BlogController extends Controller implements ControllerInterface
{
    /**
     * @var PostModel
     */
    protected $postModel;

    /**
     * @var CategoryModel
     */
    protected $categoryModel;

    /**
     * Affiche l'ensemble des Posts selon la LIMIT
     *
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
        $nbPosts = $this->postModel->count();

        $paginationOptions = $this->pagination($vars, $nbPosts);

        $posts = $this->postModel->findAll(
            $paginationOptions['start'],
            $paginationOptions['limit'],
            true,
            ' ORDER BY updatedAt DESC '
        );

        $categories = $this->categoryModel->findAll();

        if ($paginationOptions['id'] <= $paginationOptions['pageNb']) {
            $this->render('blog/index.twig', compact('posts', 'paginationOptions', 'categories'));
        } else {
            $this->render404();
        }
    }

    /**
     * Affiche un Post particulier
     *
     * @param array $vars
     * @return void
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \Exception
     */
    public function show(array $vars): void
    {
        $post = $this->postModel->find($vars['id']);
        $category = $this->categoryModel->find($post->getCategory());

        if ($post) {
            $this->render('blog/show.twig', compact('post', 'category'));
        } else {
            $this->render404();
        }
    }

    /**
     * Affiche les articles triés selon la catégorie
     *
     * @param array $vars
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function category(array $vars): void
    {
        $categoryId = $this->categoryModel->findBySlug($vars['slug']);

        $nbPosts = $this->postModel->count('category', $categoryId);

        $paginationOptions = $this->pagination($vars, $nbPosts);

        $posts = $this->postModel->findAllByCategory(
            $categoryId,
            $paginationOptions['start'],
            $paginationOptions['limit'],
            true,
            ' ORDER BY updatedAt DESC '
        );

        $categories = $this->categoryModel->findAll();

        $categoryName = $posts[0]->getCategory();

        if ($paginationOptions['id'] <= $paginationOptions['pageNb']) {
            $this->render(
                'blog/category.twig',
                compact('posts', 'paginationOptions', 'categories', 'categoryName', 'vars')
            );
        } else {
            $this->render404();
        }
    }
}
