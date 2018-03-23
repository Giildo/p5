<?php

namespace Core\ORM\Classes;

use Exception;

class ORMException extends Exception
{
    /**
     * @var int
     */
    const NO_ELEMENT = 100;

    public function __construct(string $message, ?int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
