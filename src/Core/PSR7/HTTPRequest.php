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

    /**
     * Récupère et renvoie les informations de sessions sous forme de tableau
     *
     * @param array|string $param
     * @return mixed
     */
    public function getSessionParam($param)
    {
        $results = [];

        if (is_array($param)) {
            if (!empty($param)) {
                foreach ($param as $item) {
                    $results[$item] = $_SESSION[$item];
                }

                return $results;
            } else {
                return null;
            }
        } elseif (is_string($param)) {
            return (isset($_SESSION[$param])) ? $_SESSION[$param] : null;
        }

        return null;
    }

    /**
     * Stocke dans la variable de session l'uri passée et l'actuel pour les méthodes qui ont besoin de l'uri passée
     */
    public function paths(): void
    {
        $_SESSION['paths']['past'] = $_SESSION['paths']['current'];
        $_SESSION['paths']['current'] = $this->getServerParam('REQUEST_URI');
    }
}
