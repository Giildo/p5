<?php

namespace Core;

use Core\PSR7\HTTPRequest;
use Core\Router\Router;
use Psr\Container\ContainerInterface;

/**
 * Class App
 * @package Core
 */
class App
{
    /**
     * @var App
     */
    private static $app = null;

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
     * @param ContainerInterface $container
     * @return App
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function init(ContainerInterface $container): App
    {
        if (self::$app === null) {
            self::$app = new App(
                $container->get(Router::class),
                $container->get(HTTPRequest::class)
            );
        }

        return self::$app;
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
     */
    public function run()
    {

    }
}
