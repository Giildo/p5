<?php

use function \DI\get;

return [
    'app.prefix' => '\App',
    'app.routes' => __DIR__ . '/routes.xml',

    'db.name'     => 'app',
    'db.user'     => 'root',
    'db.password' => 'jOn79613226',
    'db.host'     => 'localhost',

    'twig.pathViews' => dirname(__DIR__, 3) . '/views',
    'twig.options'   => [],

    'blog.limit.post'      => 9,
    'admin.limit.post'     => 10,
    'admin.limit.category' => 10,

    'users'      => get(\App\Admin\Model\UserModel::class),
    'posts'      => get(\App\Blog\Model\PostModel::class),
    'categories' => get(\App\Blog\Model\CategoryModel::class),
    'comments'   => get(\App\Blog\Model\CommentModel::class),
    'admin'      => get(\App\Admin\Model\AdminModel::class)
];
