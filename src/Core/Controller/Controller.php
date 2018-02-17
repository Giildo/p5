<?php

namespace Core\Controller;

use Psr\Container\ContainerInterface;
use Twig_Environment;

/**
 * Class Controller
 * @package Core\Controller
 */
class Controller implements ControllerInterface
{
    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Controller constructor.
     *
     * @param Twig_Environment $twig
     * @param ContainerInterface $container
     * @param array|null $models
     */
    public function __construct(Twig_Environment $twig, ContainerInterface $container, ?array $models = [])
    {
        $this->twig = $twig;
        $this->container = $container;
    }

    /**
     * Lance la méthode passée en paramètres en lui ajoutant si besoin des les paramètres
     *
     * @param string $nameMethod
     * @param array|null $vars
     * @return void
     * @throws \Exception
     */
    public function run(string $nameMethod, ?array $vars = []): void
    {
        if (is_callable([$this, $nameMethod])) {
            $this->$nameMethod($vars);
        } else {
            throw new \Exception('The method called isn\'t a class method');
        }
    }

    /**
     * Envoie une vue Twig avec les éléments nécessaire à son traitement
     *
     * @param string $nameView
     * @param array|null $twigVariable
     * @return void
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function render(string $nameView, ?array $twigVariable = []): void
    {
        echo $this->twig->render($nameView, $twigVariable);
    }

    /**
     * Envoie une vue Twig pour la page 404
     *
     * @return void
     */
    protected function render404(): void
    {
        header('HTTP/1.0 404 Not Found');
        header('Location: /404');
    }
}
