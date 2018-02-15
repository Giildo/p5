<?php

namespace Core\Router;

use Core\Controller\ControllerInterface;

class Route
{
    /**
     * @var string
     */
    private $name;

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

    public function __construct(string $name, string $path, ControllerInterface $controller, string $nameMethod)
    {
        $this->name = $name;
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

    /**
     * @return ControllerInterface
     */
    public function getController(): ControllerInterface
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function getNameMethod(): string
    {
        return $this->nameMethod;
    }
}
