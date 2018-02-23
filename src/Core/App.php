<?php

namespace Core;

use Core\Auth\DBAuth;
use Core\Controller\Controller;
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
     * @var DBAuth
     */
    private $auth;

    /**
     * App constructor.
     * @param Router $router
     * @param HTTPRequest $request
     * @param ContainerInterface $container
     * @param DBAuth $auth
     */
    public function __construct(Router $router, HTTPRequest $request, ContainerInterface $container, DBAuth $auth)
    {
        $this->request = $request;
        $this->router = $router;
        $this->container = $container;
        $this->auth = $auth;

        $this->request->paths();
    }

    /**
     * Lance l'application
     *
     * Récupère le Router, lui fait trouver le bon controlleur, l'instancie et lance la vue
     * @throws \Exception
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
        $extractPathRoute = explode('/', $route->getPath());

        // Vérifie que si la route commence par un "admin", le User est bien connecté sinon le renvoie sur NotLog
        if ($extractPathRoute[1] === 'admin') {
            if (!$this->auth->logged()) {
                $this->container->get(Controller::class)->renderNotLog();
            }
        }
        // Pour la partie admin ajout du suffixe "admin" à la config
        if (isset($extractNameRoute[2])) {
            $models = $extractNameRoute[0] . '.' . $extractNameRoute[1] . '.models';
        } else {
            $models = $extractNameRoute[0] . '.models';
        }

        $models = (!empty($this->container->get($models))) ? $this->container->get($models) : [];

        return new $controller(
            $this->container->get(Twig_Environment::class),
            $this->container,
            $models
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
