<?php

namespace Core;

use Core\Database\Database;
use Core\PSR7\HTTPRequest;
use Core\Router\Router;

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
     * App constructor.
     * @param Router $router
     * @param HTTPRequest $request
     */
    public function __construct(Router $router, HTTPRequest $request)
    {
        $this->request = $request;
        $this->router = $router;
    }

    /**
     * Lance l'application
     * Récupère le Router lui fait trouver le bon controlleur et lance la vue
     * @throws \Exception
     */
    public function run(): void
    {
        $uri = $this->request->getServerParam('REQUEST_URI');

        $route = $this->router->getRoute($uri);

        $controller = $route->getController();

        $controller->run($route->getNameMethod(), $route->getVars());
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
