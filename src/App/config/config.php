<?php

use function DI\get;

return [
    'app.prefix' => '\App',
    'app.routes' => __DIR__ . '/routes.xml',

    \Core\Router\Router::class    => \DI\object()->constructor(get('app.prefix'), get('app.routes')),
    \Core\PSR7\HTTPRequest::class => \DI\object()
];