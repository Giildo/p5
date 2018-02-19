<?php

namespace Core\Controller;

use Psr\Container\ContainerInterface;
use Twig_Environment;

/**
 * Interface ControllerInterface
 * @package Core\Controller
 */
interface ControllerInterface
{
    /**
     * Controller constructor.
     *
     * @param Twig_Environment $twig
     * @param ContainerInterface $container
     * @param array|null $models
     */
    public function __construct(Twig_Environment $twig, ContainerInterface $container, ?array $models = []);

    /**
     * Lance la méthode passée en paramètres en lui ajoutant si besoin des les paramètres
     *
     * @param string $nameMethod
     * @param array|null $vars
     * @return void
     * @throws \Exception
     */
    public function run(string $nameMethod, ?array $vars = []): void;
}
