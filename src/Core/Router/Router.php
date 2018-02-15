<?php

namespace Core\Router;

use App\Blog\Controller\BlogController;
use App\Blog\Model\PostModel;
use Core\Controller\ControllerInterface;
use Psr\Container\ContainerInterface;

/**
 * Class Router
 * @package Core\Router
 */
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

            $controller = $this->namespace . '\\' . $controllerType . '\\Controller\\' . $controllerType . 'Controller';

            $calledController = new $controller(
                $this->container->get(\Twig_Environment::class),
                $this->container->get(PostModel::class)
            );

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
