<?php

namespace Core\Router;

use Psr\Container\ContainerInterface;

/**
 * Classes Router
 * @package Core\Router
 */
class Router
{
    /**
     * @var Route[]
     */
    public $routes;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Router constructor.
     * @param string $namespace
     * @param null|string $configFile
     * @param ContainerInterface $container
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(string $namespace, ?string $configFile = null, ContainerInterface $container)
    {
        $this->namespace = $namespace;
        $this->container = $container;

        if ($configFile !== null) {
            $this->addRoutesWithConfigFiles($configFile);
        }
    }

    /**
     * @param string $name
     * @param string $path
     * @param string $controller
     * @param string $method
     * @throws \Exception
     */
    public function addRoute(
        string $name,
        string $path,
        string $controller,
        string $method
    ): void {
        if (!isset($this->routes[$name])) {
            $this->routes[$name] = new Route($name, $path, $controller, $method);
        } else {
            throw new \Exception('The Route already exists');
        }
    }

    /**
     * @param string $configFile
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function addRoutesWithConfigFiles(string $configFile): void
    {
        $xml = new \DOMDocument();

        $xml->load($configFile);

        $routes = $xml->getElementsByTagName('route');

        /** @var \DOMElement $route */
        foreach ($routes as $route) {
            $controllerType = ucfirst($route->getAttribute('controller'));
            $part = explode('_', $route->getAttribute('name'));

            $controller =
                $this->namespace .
                '\\' .
                ucfirst($part[0]) .
                '\\Controller\\' .
                $controllerType .
                'Controller';

            $this->addRoute(
                $route->getAttribute('name'),
                $route->getAttribute('path'),
                $controller,
                $route->getAttribute('method')
            );
        }
    }

    /**
     * @param string $uri
     * @return Route|null
     */
    public function getRoute(string $uri): ?Route
    {

        if ($uri === __ROOT__ . '' || $uri === __ROOT__ . '/') {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . __ROOT__ . '/accueil');
            return $this->routes['general_accueil'];
        }

        foreach ($this->routes as $route) {
            if ($route->comparePath($uri)) {
                return $route;
            }
        }

        header('HTTP/1.0 404 Not Found');
        header('Location: ' . __ROOT__ . '/404');
        return null;
    }
}
