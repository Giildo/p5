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
     * @var Database
     */
    private $database;

    /**
     * App constructor.
     * @param Router $router
     * @param HTTPRequest $request
     * @param Database $database
     */
    public function __construct(Router $router, HTTPRequest $request, Database $database)
    {
        $this->request = $request;
        $this->router = $router;
        $this->database = $database;
    }

    /**
     * @return HTTPRequest
     */
    public function getRequest(): HTTPRequest
    {
        return $this->request;
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

        $controller->run($route->getNameMethod());
    }

    public function getRouter()
    {
        return $this->router;
    }
}
