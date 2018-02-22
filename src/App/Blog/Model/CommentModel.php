<?php

namespace App\Blog\Model;

use App\Entity\Comment;
use Core\Model\Model;
use PDO;

class CommentModel extends Model
{
    protected $table = 'comments';

    /**
     * Récupère tous les commentaire d'un article
     *
     * @param int $postId
     * @param int|null $start
     * @param int|null $limit
     * @param bool|null $order
     * @param null|string $orderBy
     * @return array
     */
    public function findAllByPost(
        int $postId,
        ?int $start = null,
        ?int $limit = null,
        ?bool $order = false,
        ?string $orderBy = null
    ): array {
        $statement = "
                SELECT  c.id,
                        c.comment,
                        c.createdAt,
                        c.updatedAt,
                        c.user as userId,
                        c.post as postId,
                        u.pseudo as user,
                        p.name as post
                FROM comments c
                LEFT JOIN users u ON c.user = u.id
                LEFT JOIN posts p ON c.post = p.id
                WHERE c.post=:postId";

        return $this->findAllByColumn(
            $statement,
            ':postId',
            $postId,
            Comment::class,
            $start,
            $limit,
            $order,
            $orderBy
        );
    }

    /**
     * Ajoute un commentaire à la base de données
     *
     * @param string $comment
     * @param int $userId
     * @param int $postId
     * @return bool
     */
    public function addComment(string $comment, int $userId, int $postId): bool
    {
        $result = $this->pdo->prepare("INSERT INTO comments (comment, createdAt, updatedAt, user, post)
                                                VALUES (:comment, NOW(), NOW(), :userId, :postId);");
        $result->bindParam('comment', $comment);
        $result->bindParam('userId', $userId, PDO::PARAM_INT);
        $result->bindParam('postId', $postId, PDO::PARAM_INT);
        return $result->execute();
    }

    /**
     * Ajoute un commentaire à la base de données
     *
     * @param string $comment
     * @param int $id
     * @param int $userId
     * @param int $postId
     * @return bool
     */
    public function updateComment(string $comment, int $id, int $userId, int $postId): bool
    {
        $result = $this->pdo->prepare(
            "UPDATE comments
            SET comment=:comment, updatedAt=NOW(), user=:userId, post=:postId WHERE id=:id;");
        $result->bindParam('comment', $comment);
        $result->bindParam('userId', $userId, PDO::PARAM_INT);
        $result->bindParam('postId', $postId, PDO::PARAM_INT);
        $result->bindParam('id', $id, PDO::PARAM_INT);
        return $result->execute();
    }
}
