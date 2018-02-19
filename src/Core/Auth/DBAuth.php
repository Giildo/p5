<?php

namespace Core\Auth;

use Core\Database\Database;
use Core\PSR7\HTTPRequest;

class DBAuth
{
    /**
     * @var Database
     */
    private $database;

    /**
     * @var HTTPRequest
     */
    private $request;

    public function __construct(Database $database, HTTPRequest $request)
    {
        $this->database = $database;
        $this->request = $request;

        session_start();
    }

    public function logged(): bool
    {
        return ($this->request->getSessionParam('confirmConnect') !== null) ?
            $this->request->getSessionParam('confirmConnect') : false;
    }
}
