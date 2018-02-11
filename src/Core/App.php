<?php

namespace Core;

use Core\PSR7\HTTPRequest;

class App
{
    /**
     * @var App
     */
    private static $app = null;

    /**
     * @var HTTPRequest
     */
    private $request;

    /**
     * App constructor.
     */
    public function __construct()
    {
        $this->request = new HTTPRequest();
    }

    /**
     * @return App
     */
    public static function init(): App
    {
        if (self::$app === null) {
            self::$app = new App();
        }

        return self::$app;
    }

    /**
     * @return HTTPRequest
     */
    public function getRequest(): HTTPRequest
    {
        return $this->request;
    }
}
