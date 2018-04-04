<?php

use function \DI\get;

return [
    'app.prefix' => '\App',
    'app.routes' => __DIR__ . '/routes.xml',

    'orm.config' => __DIR__ . '/orm_config.php',

    'db.name'     => 'jojotiquac7961',
    'db.user'     => 'jojotiquac7961',
    'db.password' => 'jOn79613226',
    'db.host'     => 'jojotiquac7961.mysql.db',

    'twig.pathViews' => dirname(__DIR__, 3) . '/views',
    'twig.options'   => [
        'cache' => dirname(__DIR__, 3) . '/cache'
    ],

    'root' => '/p5',

    'blog.limit.post'      => 9,
    'admin.limit.post'     => 10,
    'admin.limit.category' => 10,
    'admin.limit.user'     => 10,

    'users'      => get(App\Models\UserModel::class),
    'posts'      => get(App\Models\PostModel::class),
    'categories' => get(App\Models\CategoryModel::class),
    'comments'   => get(App\Models\CommentModel::class),
    'admin'      => get(App\Models\AdminModel::class)
];
