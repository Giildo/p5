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

        $this->render('blog/index.twig', compact('posts'));
    }

    /**
     *
     */
    public function show()
    {
        // TODO: Implement show() method.
    }
}
