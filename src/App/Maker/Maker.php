<?php

namespace App\Maker;

use Faker\Factory;
use PDO;

class Maker
{
    public function run(PDO $pdo): void
    {
        $posts = [];
        $faker = Factory::create();

        for ($i = 0 ; $i < 150 ; $i++) {
            $date = $faker->date('Y-m-d H:i:s');
            $posts[] = [
                'title' => $faker->sentence(6),
                'content' => $faker->text(500),
                'createdAt' => $date,
                'updatedAt' => $date,
                'user' => $faker->randomElement(['1', '2']),
                'category' => $faker->randomElement(['1', '2', '3', '4'])
            ];
        }

        foreach ($posts as $post) {
            $pdo->query(
                "INSERT INTO posts (title, content, createdAt, updatedAt, category, user) 
                          VALUES ('{$post['title']}', '{$post['content']}', '{$post['createdAt']}', '{$post['updatedAt']}', '{$post['category']}', '{$post['user']}')");
        }
    }
}
