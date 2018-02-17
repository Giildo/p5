<?php

namespace Core;

use Core\Controller\ControllerInterface;
use Core\PSR7\HTTPRequest;
use Core\Router\Route;
use Core\Router\Router;
use Psr\Container\ContainerInterface;
use Twig_Environment;

/**
 * Class App
 * @package Core
 */
class App
{
    /**
     * @var HTTPRequest
     */
    private $request;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * App constructor.
     * @param Router $router
     * @param HTTPRequest $request
     * @param ContainerInterface $container
     */
    public function __construct(Router $router, HTTPRequest $request, ContainerInterface $container)
    {
        $this->request = $request;
        $this->router = $router;
        $this->container = $container;
    }

    /**
     * Lance l'application
     *
     * Récupère le Router, lui fait trouver le bon controlleur, l'instancie et lance la vue
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function run(): void
    {
        $uri = $this->request->getServerParam('REQUEST_URI');

        $route = $this->router->getRoute($uri);

        $controller = $this->newController($route);

        $controller->run($route->getNameMethod(), $route->getVars());
    }

    /**
     * Récupère la route, le nom de la config pour les modèles et instancie le bon Controller
     *
     * @param Route $route
     * @return ControllerInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function newController(Route $route): ControllerInterface
    {
        $controller = $route->getController();

        // Récupération du nom de la Route pour permettre de récupérer le nom de la config pour les models
        $extractNameRoute = explode('_', $route->getName());
        $models = $extractNameRoute[0] . '.models';

        return new $controller(
            $this->container->get(Twig_Environment::class),
            $this->container,
            $this->container->get($models)
        );
    }

    /**
     * @return HTTPRequest
     */
    public function getRequest(): HTTPRequest
    {
        return $this->request;
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
}
