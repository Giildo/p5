<?php

namespace Core\Exception;

use Exception;

class JojotiqueException extends Exception
{
    public const BAD_PASSWORD = 10;
    public const USER_IS_NULL = 11;
    public const PASSWORD_IS_NULL = 12;

    public const ROUTE_METHOD_ERROR = 20;

    public function __construct(string $message = "", ?int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
