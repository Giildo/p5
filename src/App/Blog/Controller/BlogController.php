<?php

namespace App\Blog\Controller;

use App\Blog\Model\PostModel;
use Core\Controller\Controller;
use Core\Controller\ControllerInterface;
use Twig_Environment;

class BlogController extends Controller implements ControllerInterface
{
    /**
     * @var PostModel
     */
    private $postModel;

    /**
     * @var int
     */
    protected const LIMIT = 10;

    /**
     * BlogController constructor.
     * @param Twig_Environment $twig
     * @param PostModel $postModel
     */
    public function __construct(Twig_Environment $twig, PostModel $postModel)
    {
        parent::__construct($twig);

        $this->postModel = $postModel;
    }

    /**
     * @param array $vars
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index(array $vars)
    {
        $postNb = $this->postModel->count();

        $id = $vars['id'];

        $pageNb = ceil($postNb / self::LIMIT);
        $start = (self::LIMIT * ($id - 1));

        $posts = $this->postModel->findAll($start, self::LIMIT);

        $next = ($id + 1 <= $pageNb) ? $id + 1 : null;
        $previous = ($id - 1 >= 1) ? $id - 1 : null;

        if ($id <= $pageNb) {
            $this->render('blog/index.twig', compact('posts', 'pageNb', 'next', 'previous'));
        } else {
            $this->render('404.twig', ['erreur' => 'La page demandée n\'existe pas']);
        }
    }

    /**
     * @param array $vars
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \Exception
     */
    public function show(array $vars)
    {
        $post = $this->postModel->find($vars['id']);

        if ($post) {
            $this->render('blog/show.twig', compact('post'));
        } else {
            $this->render('404.twig', ['erreur' => 'La page demandée n\'existe pas']);
        }
    }
}
