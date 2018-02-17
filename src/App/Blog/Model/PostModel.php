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
                SELECT posts.id, posts.name, posts.content, posts.createdAt, posts.updatedAt, categories.name as category
                FROM `{$this->table}`
                LEFT JOIN categories ON category = categories.id
                WHERE category=:categoryId
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
}
