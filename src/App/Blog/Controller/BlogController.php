<?php

namespace App\Blog\Controller;

use App\Blog\Model\PostModel;
use Core\Controller\Controller;
use Core\Controller\ControllerInterface;
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
     * Limite d'affichage du nombre d'article par page
     * @var int
     */
    protected const LIMIT = 8;

    /**
     * BlogController constructor.
     *
     * @param Twig_Environment $twig
     * @param PostModel $postModel
     */
    public function __construct(Twig_Environment $twig, PostModel $postModel)
    {
        parent::__construct($twig);

        $this->postModel = $postModel;
    }

    /**
     * Affiche l'ensemble des Posts selon la LIMIT
     *
     * @param array $vars
     * @return void
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index(array $vars): void
    {
        $postNb = $this->postModel->count();

        $id = $vars['id'];

        $pageNb = ceil($postNb / self::LIMIT);
        $start = (self::LIMIT * ($id - 1));

        $posts = $this->postModel->findAll($start, self::LIMIT, true, ' ORDER BY updatedAt DESC ');

        $next = ($id + 1 <= $pageNb) ? $id + 1 : null;
        $previous = ($id - 1 >= 1) ? $id - 1 : null;

        if ($id <= $pageNb) {
            $this->render('blog/index.twig', compact('posts', 'pageNb', 'next', 'previous', 'id'));
        } else {
            $this->render('404.twig', ['erreur' => 'La page demandée n\'existe pas']);
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
            $this->render('404.twig', ['erreur' => 'La page demandée n\'existe pas']);
        }
    }
}
