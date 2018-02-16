<?php

namespace Core\Controller;

interface ControllerInterface
{
    /**
     * @param string $nameMethod
     * @param array|null $vars
     * @return string
     * @throws \Exception
     */
    public function run(string $nameMethod, ?array $vars = []);
}
