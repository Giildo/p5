<?php

namespace App\Blog\Model;

use App\Entity\Post;
use Core\Model\Model;

/**
 * Class PostModel
 * @package App\Blog\Model
 */
class PostModel extends Model
{
    /**
     * @return array
     */
    public function findAll(): array
    {
        $result = $this->pdo->query('SELECT * FROM `posts` LIMIT 10');
        $this->setFetchMode($result, Post::class);

        return $result->fetchAll();
    }

    /**
     * @param string $id
     * @return Post|bool
     */
    public function find(string $id)
    {
        $result = $this->pdo->prepare('SELECT * FROM `posts` WHERE id=:id');
        $result->bindParam(':id', $id);
        $this->setFetchMode($result, Post::class);
        $result->execute();

        return $result->fetch();
    }
}
