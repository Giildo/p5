<?php

namespace App\Blog\Controller;

use App\Blog\Model\PostModel;
use Core\Controller\Controller;
use Core\Controller\ControllerInterface;

class BlogController extends Controller implements ControllerInterface
{
    /**
     * @var PostModel
     */
    private $postModel;

    /**
     * BlogController constructor.
     * @param PostModel $postModel
     */
    public function __construct(PostModel $postModel)
    {
        $this->postModel = $postModel;

        $this->pathView = dirname(__DIR__) . '/views';
    }

    /**
     * @param string $nameMethod
     * @return string|void
     * @throws \Exception
     */
    public function run(string $nameMethod)
    {
        parent::run($nameMethod);
    }

    /**
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index()
    {
        $posts = $this->postModel->findAll();

        $this->render('index.twig', compact('posts'));
    }

    /**
     *
     */
    public function show()
    {
        // TODO: Implement show() method.
    }
}
