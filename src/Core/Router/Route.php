<?php

namespace Core\Router;

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
     * @var string
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
     * @param string $name
     * @param string $path
     * @param string $controller
     * @param string $nameMethod
     */
    public function __construct(
        string $name,
        string $path,
        string $controller,
        string $nameMethod
    ) {
        $this->name = $name;
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
     * @return string
     */
    public function getController(): string
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

        preg_match_all('#{([a-zA-Z_]+): \\\\{1}[a-z]{1}\\+?}#', $routePath, $matches);

        foreach ($matches[1] as $match) {
            $this->vars[] = $match;
        }

        return preg_replace('#{[a-zA-Z_]+: (\\\\{1}[a-z]{1}\\+?)}#', '($1)', $routePath);
    }

    public function getVars()
    {
        return $this->vars;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
