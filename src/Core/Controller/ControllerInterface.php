<?php

namespace Core\Controller;

/**
 * Interface ControllerInterface
 * @package Core\Controller
 */
interface ControllerInterface
{
    /**
     * @param string $nameMethod
     * @param array|null $vars
     * @return void
     */
    public function run(string $nameMethod, ?array $vars = []): void;
}
