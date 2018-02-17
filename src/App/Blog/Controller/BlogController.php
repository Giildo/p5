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
    private $postModel;

    /**
     * @var CategoryModel
     */
    private $categoryModel;

    public function __construct(Twig_Environment $twig, ContainerInterface $container, ?array $models = [])
    {
        parent::__construct($twig, $container, $models);

        $this->InstantiationModels($models);
    }

    use InstantiationModels;

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
            ' ORDER BY updatedAt DESC ');

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
            ' ORDER BY updatedAt DESC ');

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

    /**
     * @param array $vars
     * @param int $nbItem
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function pagination(array $vars, int $nbItem): array
    {
        $pagination = [];

        $pagination['limit'] = $this->container->get('blog.limit.post');

        $pagination['id'] = $vars['id'];

        $pagination['pageNb'] = ceil($nbItem / $pagination['limit']);
        $pagination['start'] = ($pagination['limit'] * ($pagination['id'] - 1));

        $pagination['next'] = ($pagination['id'] + 1 <= $pagination['pageNb']) ? $pagination['id'] + 1 : null;
        $pagination['previous'] = ($pagination['id'] - 1 >= 1) ? $pagination['id'] - 1 : null;

        return $pagination;
    }
}
