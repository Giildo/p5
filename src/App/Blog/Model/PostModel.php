<?php

namespace App\Blog\Model;

use App\Entity\Post;
use Core\Model\Model;
use PDO;

class PostModel extends Model
{
    public function findAll()
    {
        $pdo = $this->database->getPDO();

        $result = $pdo->query('SELECT * FROM `posts` LIMIT 10');
        $result->setFetchMode(PDO::FETCH_CLASS, Post::class);

        return $result->fetchAll();
    }
}
