<?php

namespace Core\PSR7;

/**
 * Class HTTPRequest
 * @package Core\PSR7
 */
class HTTPRequest
{
    /**
     * Renvoie les données envoyés par les méthodes POST et GET
     *
     * @param null|string $nameParam
     * @return null|string|string[]
     */
    public function getRequestParam(?string $nameParam = null)
    {
        if (!empty($_REQUEST)) {
            if ($nameParam === null) {
                return $_REQUEST;
            } elseif (isset($_REQUEST[$nameParam])) {
                return $_REQUEST[$nameParam];
            } else {
                return null;
            }
        }
        return null;
    }

    /**
     * Renvoie les données envoyées par le serveur
     *
     * @param string|null $nameParam
     * @return array|string
     */
    public function getServerParam(?string $nameParam = null)
    {
        return ($nameParam === null) ? $_SERVER : $_SERVER[$nameParam];
    }
}
