<?php

namespace Core\Controller;

use Twig_Environment;
use Twig_Loader_Filesystem;

/**
 * Class Controller
 * @package Core\Controller
 */
class Controller implements ControllerInterface
{
    /**
     * @var string|null
     */
    protected $pathView = null;

    /**
     * @var array
     */
    protected $twigOptions = [];

    /**
     * @param string $nameMethod
     * @return string
     * @throws \Exception
     */
    public function run(string $nameMethod)
    {
        if (is_callable([$this, $nameMethod])) {
            $this->$nameMethod();
        } else {
            throw new \Exception('The method called isn\'t a class method');
        }
    }

    /**
     * @param string $nameView
     * @param array $twigVariable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function render(string $nameView, array $twigVariable)
    {
        $loader = new Twig_Loader_Filesystem($this->pathView);
        $twig = new Twig_Environment($loader, $this->twigOptions);

        echo $twig->render($nameView, $twigVariable);
    }
}
