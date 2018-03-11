<?php

namespace App\Blog\Model;

use App\Entity\Post;
use Core\Model\Model;
use PDO;

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
     * Récupère un post en fonction de la catégorie
     *
     * @param string $categorySlug
     * @param int|null $start
     * @param int|null $limit
     * @param bool|null $order
     * @param null|string $orderBy
     * @return array
     */
    public function findAllByCategory(
        string $categorySlug,
        ?int $start,
        ?int $limit,
        ?bool $order = false,
        ?string $orderBy = null
    ): array {
        $orderBy = ($order) ? $orderBy : '';

        $startLimit = '';
        if ($start !== null && $limit !== null) {
            $startLimit = 'LIMIT :limit OFFSET :start';
        }

        $result = $this->pdo->prepare("
                SELECT  posts.id,
                        posts.title,
                        posts.content,
                        posts.createdAt,
                        posts.updatedAt,
                        categories.name as category,
                        categories.slug as categorySlug
                FROM posts
                INNER JOIN categories ON posts.category = categories.id
                WHERE categories.slug=:slug
                {$orderBy}
                {$startLimit}");

        if ($start !== null && $limit !== null) {
            $result->bindParam(':start', $start, PDO::PARAM_INT);
            $result->bindParam(':limit', $limit, PDO::PARAM_INT);
        }

        $result->bindParam(':slug', $categorySlug);
        $result->setFetchMode(PDO::FETCH_CLASS, Post::class);
        $result->execute();

        return $result->fetchAll();
    }

    /**
     * Récupère un post de son ID avec sa catégorie
     *
     * @param int $id
     * @return Post
     */
    public function findPostWithCategoryAndUser(int $id): Post
    {
        $result = $this->pdo->prepare("
                SELECT  posts.id,
                        posts.title,
                        posts.content,
                        posts.createdAt,
                        posts.updatedAt,
                        categories.name as category,
                        categories.slug as categorySlug,
                        users.pseudo as user
                FROM posts
                LEFT JOIN categories
                  ON posts.category = categories.id
                LEFT JOIN users
                  ON posts.user = users.id
                WHERE posts.id = :id");

        $result->bindParam(':id', $id);
        $result->setFetchMode(PDO::FETCH_CLASS, Post::class);
        $result->execute();

        return $result->fetch();
    }

    /**
     * Récupère les posts en fonction de l'auteur
     *
     * @param int $userId
     * @param int|null $start
     * @param int|null $limit
     * @param bool|null $order
     * @param null|string $orderBy
     * @param bool|null $admin
     * @return array
     */
    public function findAllByUserAndCategory(
        int $userId,
        ?int $start,
        ?int $limit,
        ?bool $order = false,
        ?string $orderBy = null,
        ?bool $admin = false
    ): array {
        $orderBy = ($order) ? $orderBy : '';

        $startLimit = '';
        $where = 'WHERE posts.user=:userId';

        if ($admin) {
            $where = '';
        }

        if ($start !== null && $limit !== null) {
            $startLimit = 'LIMIT :limit OFFSET :start';
        }

        $result =
            $this->pdo->prepare("
                    SELECT  posts.id,
                            posts.title,
                            posts.content,
                            posts.createdAt,
                            posts.updatedAt,
                            categories.name AS category,
                            users.pseudo AS user
            FROM posts
            LEFT JOIN categories ON posts.category = categories.id
            LEFT JOIN users ON posts.user = users.id
            {$where}
            {$orderBy}
            {$startLimit}");

        if ($start !== null && $limit !== null) {
            $result->bindParam(':start', $start, PDO::PARAM_INT);
            $result->bindParam(':limit', $limit, PDO::PARAM_INT);
        }

        if (!$admin) {
            $result->bindParam(':userId', $userId);
        }

        $result->setFetchMode(PDO::FETCH_CLASS, Post::class);
        $result->execute();

        return $result->fetchAll();
    }

    /**
     * Renvoie les données à la BD pour adapter les éléments de post
     *
     * @param int $userId
     * @param int $categoryId
     * @param array $posts
     * @param int $postId
     * @return bool
     */
    public function updatePost(int $userId, int $categoryId, array $posts, int $postId): bool
    {
        foreach ($posts as $post) {
            if (empty($post)) {
                return false;
            }
        }

        $result = $this->pdo->prepare("UPDATE posts
        SET `category` = :category,
            `name` = :name,
            `content` = :content,
            `updatedAt` = NOW(),
            `user` = :user
        WHERE id = :id");

        $result->bindParam('category', $categoryId, PDO::PARAM_INT);
        $result->bindParam('name', $posts['name']);
        $result->bindParam('content', $posts['content']);
        $result->bindParam('user', $userId, PDO::PARAM_INT);
        $result->bindParam('id', $postId, PDO::PARAM_INT);
        return $result->execute();
    }

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
