<?php

use App\Admin\Model\UserModel;
use App\Blog\Model\CategoryModel;
use App\Blog\Model\PostModel;
use Core\App;
use Core\Auth\DBAuth;
use Core\Database\Database;
use Core\Form\Form;
use Core\Model\Model;
use Core\PSR7\HTTPRequest;
use Core\Router\Router;
use function DI\get;
use function DI\object;
use Psr\Container\ContainerInterface;

return [
    'app.prefix' => '\App',
    'app.routes' => __DIR__ . '/routes.xml',

    'db.name'     => 'app',
    'db.user'     => 'root',
    'db.password' => 'jOn79613226',
    'db.host'     => 'localhost',

    'twig.pathViews' => dirname(__DIR__, 3) . '/views',
    'twig.options'   => [],

    'general.models' => [],

    'blog.models'     => [
        'post'     => get(PostModel::class),
        'category' => get(CategoryModel::class)
    ],

    'admin.models' => [],

    'admin.user.models' => [
        'user' => get(UserModel::class)
    ],

    'admin.post.models' => [
        'post' => get(PostModel::class)
    ],

    'admin.category.models' => [
        'category' => get(CategoryModel::class)
    ],

    'blog.limit.post' => 9,
    'admin.limit.post' => 10,

    App::class         => object(),
    Router::class      => object()->constructor(
        get('app.prefix'),
        get('app.routes'),
        get(ContainerInterface::class)
    ),
    Form::class        => object(),
    DBAuth::class      => object(),
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
    },

    Twig_Loader_Filesystem::class => object()->constructor(get('twig.pathViews')),
    Twig_Environment::class       => object()->constructor(
        get(Twig_Loader_Filesystem::class),
        get('twig.options')
    )
];
