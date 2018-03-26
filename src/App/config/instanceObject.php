<?php

use App\Admin\Model\UserModel;
use App\Blog\Controller\CommentController;
use Core\App;
use Core\Auth\DBAuth;
use Core\Database\Database;
use Core\Form\Form;
use Core\Model\Model;
use Core\ORM\Classes\ORMEntity;
use Core\ORM\Classes\ORMSelect;
use Core\Router\Router;
use function DI\get;
use function DI\object;
use Psr\Container\ContainerInterface;
use Core\ORM\Classes\ORMController;

return [
    Router::class => object()->constructor(
        get('app.prefix'),
        get('app.routes'),
        get(ContainerInterface::class)
    ),

    ORMController::class => object()->constructor(
        get(ContainerInterface::class),
        get(PDO::class)
    ),

    Database::class => object()->constructor(get(PDO::class)),
    PDO::class      => function (ContainerInterface $c) {
        return new PDO(
            'mysql:host=' . $c->get('db.host') . ';dbname=' . $c->get('db.name') . ';charset=utf8',
            $c->get('db.user'),
            $c->get('db.password'),
            [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION
            ]
        );
    },

    Twig_Loader_Filesystem::class => object()->constructor(get('twig.pathViews')),
    Twig_Environment::class       => object()->constructor(
        get(Twig_Loader_Filesystem::class),
        get('twig.options')
    )
];
