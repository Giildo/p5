<?php

namespace Core\Controller;

use Core\Exception\JojotiqueException;
use Core\ORM\Classes\ORMSelect;
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
     * @param ORMSelect $select
     */
    public function __construct(Twig_Environment $twig, ContainerInterface $container, ?array $models = [], ?ORMSelect $select = null);

    /**
     * Lance la méthode passée en paramètres en lui ajoutant si besoin des les paramètres
     *
     * @param string $nameMethod
     * @param array|null $vars
     * @return void
     * @throws JojotiqueException
     */
    public function run(string $nameMethod, ?array $vars = []): void;

    /**
     * Envoie une vue Twig pour la page 404
     *
     * @return void
     */
    public function render404(): void;

    /**
     * Envoie une vue Twig pour la page 404
     *
     * @return void
     */
    public function renderNotLog(): void;

    public function renderErrorNotAdmin();

    /**
     * Méthode de redirection
     *
     * @param string $path
     */
    public function redirection(string $path): void;
}
