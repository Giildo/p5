<?php

namespace Core\Router;

use Core\Controller\ControllerInterface;

class Route
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var ControllerInterface
     */
    private $controller;

    /**
     * @var string
     */
    private $nameMethod;

    public function __construct(string $path, ControllerInterface $controller, string $nameMethod)
    {
        $this->path = $path;
        $this->controller = $controller;
        $this->nameMethod = $nameMethod;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
