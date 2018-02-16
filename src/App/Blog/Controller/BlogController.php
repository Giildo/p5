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
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index()
    {
        $posts = $this->postModel->findAll();

        $this->render('blog/index.twig', compact('posts'));
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
            throw new \Exception('The post\'s ID isn\'t true');
        }
    }
}
