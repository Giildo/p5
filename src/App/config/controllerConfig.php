<?php

use App\Admin\Model\AdminModel;
use App\Admin\Model\UserModel;
use App\Blog\Model\CategoryModel;
use App\Blog\Model\CommentModel;
use App\Blog\Model\PostModel;
use function DI\get;

return [
    'general.models' => [],

    'general.error.models' => [],

    'blog.post.models' => [
        'post'     => get(PostModel::class),
        'category' => get(CategoryModel::class),
        'user'     => get(UserModel::class),
        'admin'    => get(AdminModel::class),
        'comment'  => get(CommentModel::class)
    ],

    'admin.models' => [
        'user' => get(UserModel::class)
    ],

    'admin.user.models' => [
        'user'  => get(UserModel::class),
        'admin' => get(AdminModel::class)
    ],

    'admin.post.models' => [
        'post'     => get(PostModel::class),
        'category' => get(CategoryModel::class),
        'user'     => get(UserModel::class),
        'admin'    => get(AdminModel::class)
    ],

    'admin.category.models' => [
        'category' => get(CategoryModel::class)
    ]
];
