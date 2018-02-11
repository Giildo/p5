<?php

namespace Core\PSR7;

class HTTPRequest
{
    /**
     * @return array|null
     */
    public function getRequestParams(): ?array
    {
        return (!empty($_REQUEST)) ? $_REQUEST : null;
    }

    /**
     * @param null|string $nameParam
     * @return null|string
     */
    public function getRequestParam(?string $nameParam = null): ?string
    {
        return (!empty($_REQUEST) && isset($_REQUEST[$nameParam])) ? $_REQUEST[$nameParam] : null;
    }

    /**
     * @param string|null $nameParam
     * @return array|string
     */
    public function getServerParam(?string $nameParam = null)
    {
        return ($nameParam === null) ? $_SERVER : $_SERVER[$nameParam];
    }
}
