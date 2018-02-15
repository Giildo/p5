<?php

use Core\App;
use Core\Database\Database;
use Core\Model\Model;
use Core\PSR7\HTTPRequest;
use Core\Router\Router;
use function DI\get;
use function DI\object;
use Psr\Container\ContainerInterface;

return [
    'app.prefix' => '\App',
    'app.routes' => __DIR__ . '/routes.xml',

    'db.name'     => 'blog',
    'db.user'     => 'root',
    'db.password' => 'jOn79613226',
    'db.host'     => 'localhost',

    App::class         => object(),
    Router::class      => object()->constructor(
        get('app.prefix'),
        get('app.routes'),
        get(ContainerInterface::class)
    ),
    HTTPRequest::class => object(),
    Database::class    => object()->constructor(get(PDO::class)),
    Model::class       => object(),
    Controller::class  => object(),
    PDO::class         => function (ContainerInterface $c) {
        return new PDO(
            'mysql:host=' . $c->get('db.host') . ';dbname=' . $c->get('db.name') . ';charset=utf8',
            $c->get('db.user'),
            $c->get('db.password'),
            [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION
            ]
        );
    }
];
