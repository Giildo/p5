<?php

namespace Core\Controller;

interface ControllerInterface
{
    /**
     * @param string $nameMethod
     * @return string
     * @throws \Exception
     */
    public function run(string $nameMethod);
}
