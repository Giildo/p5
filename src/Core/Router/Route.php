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

    /**
     * @var array
     */
    private $vars = [];

    /**
     * Route constructor.
     * @param string $path
     * @param ControllerInterface $controller
     * @param string $nameMethod
     */
    public function __construct(
        string $path,
        ControllerInterface $controller,
        string $nameMethod
    ) {
        $this->path = $path;
        $this->controller = $controller;
        $this->nameMethod = $nameMethod;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function comparePath(string $path): bool
    {
        $routePath = $this->shortPath($this->path);
        $matches = [];
        $result = preg_match_all("#{$routePath}#", $path, $matches);

        unset($matches[0]);

        $vars = [];

        $matchNb = count($matches);

        for ($i = 0; $i < $matchNb; $i++) {
            $key = $this->vars[$i];
            $value = $matches[$i+1];

            $vars[$key] = $value[0];
        }

        $this->vars = $vars;

        return $result;
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

    /**
     * @param string $routePath
     * @return string
     */
    private function shortPath(string $routePath): string
    {
        $matches = [];

        preg_match_all('#{([a-z]+): \\\\{1}[a-z]{1}\\+?}#', $routePath, $matches);

        foreach ($matches[1] as $match) {
            $this->vars[] = $match;
        }

        return preg_replace('#{[a-z]+: (\\\\{1}[a-z]{1}\\+?)}#', '($1)', $routePath);
    }

    public function getVars()
    {
        return $this->vars;
    }
}
