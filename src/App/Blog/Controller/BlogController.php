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
        $postNb = $this->postModel->count();

        $limit = $this->container->get('blog.limit.post');

        $id = $vars['id'];

        $pageNb = ceil($postNb / $limit);
        $start = ($limit * ($id - 1));

        $posts = $this->postModel->findAll($start, $limit, true, ' ORDER BY updatedAt DESC ');

        $next = ($id + 1 <= $pageNb) ? $id + 1 : null;
        $previous = ($id - 1 >= 1) ? $id - 1 : null;

        if ($id <= $pageNb) {
            $this->render('blog/index.twig', compact('posts', 'pageNb', 'next', 'previous', 'id'));
        } else {
            $this->render('general/404.twig', ['erreur' => 'La page demandée n\'existe pas.']);
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

        if ($post) {
            $this->render('blog/show.twig', compact('post'));
        } else {
            $this->render('general/404.twig', ['erreur' => 'La page demandée n\'existe pas.']);
        }
    }
}
