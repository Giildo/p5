<?php

namespace App\Blog\Model;

use Core\Model\Model;

/**
 * Fait le lien pour les Categories
 *
 * Classes PostModel
 * @package App\Blog\Model
 */
class PostModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'posts';

    /**
     * Compte le nombre d'article en fonction du slug de la catégorie
     *
     * @param string $categorySlug
     * @return int
     */
    public function countPostByCategory(string $categorySlug): int
    {
        $result = $this->pdo->prepare(
            'SELECT COUNT(posts.id) FROM posts
                      INNER JOIN categories ON posts.category = categories.id
                      WHERE categories.slug=:slug'
        );
        $result->bindParam('slug', $categorySlug);
        $result->execute();
        return $result->fetchColumn();
    }

    /**
     * Compte le nombre d'article en fonction de l'ID de l'utilisateur
     *
     * @param int $userId
     * @return int
     */
    public function countPostsByUser(int $userId): int
    {
        $result = $this->pdo->prepare(
            'SELECT COUNT(posts.id) FROM posts
                      INNER JOIN users ON posts.user = users.id
                      WHERE users.id=:id'
        );
        $result->bindParam('id', $userId);
        $result->execute();
        return $result->fetchColumn();
    }
}
