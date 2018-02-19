<?php

namespace App\Blog\Model;

use App\Entity\Post;
use Core\Model\Model;
use PDO;

/**
 * Fait le lien pour les Categories
 *
 * Class PostModel
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
     * @param int $categoryId
     * @param int|null $start
     * @param int|null $limit
     * @param bool|null $order
     * @param null|string $orderBy
     * @return array
     */
    public function findAllByCategory(
        int $categoryId,
        ?int $start,
        ?int $limit,
        ?bool $order = false,
        ?string $orderBy = null
    ): array {
        $orderBy = ($order) ? $orderBy : '';

        if ($start === null && $limit === null) {
            $result = $this->pdo->prepare("SELECT * FROM `{$this->table}` WHERE category=:categoryId{$orderBy}");
        } else {
            $result = $this->pdo->prepare("
                SELECT  posts.id,
                        posts.name,
                        posts.content,
                        posts.createdAt,
                        posts.updatedAt,
                        categories.name as category
                FROM posts
                LEFT JOIN categories ON posts.category = categories.id
                WHERE posts.category=:categoryId
                {$orderBy}
                LIMIT :start, :limit");
            $result->bindParam(':start', $start, PDO::PARAM_INT);
            $result->bindParam(':limit', $limit, PDO::PARAM_INT);
        }
        $result->bindParam(':categoryId', $categoryId);
        $this->setFetchMode($result, Post::class);
        $result->execute();

        return $result->fetchAll();
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
            $startLimit = 'LIMIT :start, :limit';
        }

        $result =
            $this
                ->pdo
                ->prepare("
                    SELECT  posts.id,
                            posts.name,
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

        $this->setFetchMode($result, Post::class);
        $result->execute();

        return $result->fetchAll();
    }
}
