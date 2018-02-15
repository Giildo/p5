<?php

namespace Core\Controller;

use Twig_Environment;

/**
 * Class Controller
 * @package Core\Controller
 */
class Controller implements ControllerInterface
{
    /**
     * @var array
     */
    protected $twigOptions = [];

    /**
     * @var Twig_Environment
     */
    private $twig;

    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

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
     * @param array|null $twigVariable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function render(string $nameView, ?array $twigVariable = null)
    {
        echo $this->twig->render($nameView, $twigVariable);
    }
}
