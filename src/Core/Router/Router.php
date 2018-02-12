<?php

namespace Core\Router;

use Core\Controller\ControllerInterface;

class Router
{
    /**
     * @var Route[]
     */
    private $routes;

    /**
     * @var string
     */
    private $namespace;

    /**
     * Router constructor.
     * @param string $namespace
     * @param null|string $configFile
     * @throws \Exception
     */
    public function __construct(string $namespace, ?string $configFile = null)
    {
        $this->namespace = $namespace;

        if ($configFile !== null) {
            $this->addRoutesWithConfigFiles($configFile);
        }
    }

    /**
     * @param string $name
     * @param string $path
     * @param ControllerInterface $controller
     * @param string $method
     * @throws \Exception
     */
    public function addRoute(string $name, string $path, ControllerInterface $controller, string $method)
    {
        if (!isset($this->routes[$name])) {
            $this->routes[$name] = new Route($name, $path, $controller, $method);
        } else {
            throw new \Exception('The Route already exists');
        }
    }

    /**
     * @param string $configFile
     * @throws \Exception
     */
    public function addRoutesWithConfigFiles(string $configFile): void
    {
        $xml = new \DOMDocument();

        $xml->load($configFile);

        $routes = $xml->getElementsByTagName('route');

        /** @var \DOMElement $route */
        foreach ($routes as $route) {
            $controllerType = ucfirst($route->getAttribute('controller'));

            $controller = $this->namespace . '\\' . $controllerType . '\\' . $controllerType . 'Controller';

            $calledController = new $controller;

            $this->addRoute(
                $route->getAttribute('name'),
                $route->getAttribute('path'),
                $calledController,
                $route->getAttribute('method')
            );
        }
    }

    /**
     * @param string $uri
     * @return Route
     * @throws \Exception
     */
    public function getRoute(string $uri): Route
    {
        foreach ($this->routes as $route) {
            if ($route->getPath() === $uri) {
                return $route;
            }
        }

        throw new \Exception('The requested Route doesn\'t exists');
    }
}
