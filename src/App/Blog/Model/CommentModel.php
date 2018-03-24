<?php

namespace App\Blog\Model;

use App\Entity\Comment;
use Core\Model\Model;
use PDO;
use stdClass;

class CommentModel extends Model
{
    protected $table = 'comments';

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
            SET comment=:comment, updatedAt=NOW(), user=:userId, post=:postId WHERE id=:id;"
        );
        $result->bindParam('comment', $comment);
        $result->bindParam('userId', $userId, PDO::PARAM_INT);
        $result->bindParam('postId', $postId, PDO::PARAM_INT);
        $result->bindParam('id', $id, PDO::PARAM_INT);
        return $result->execute();
    }

    /**
     * Récupère les IDs de l'utilisateur, du com et du post et les renvoie
     *
     * @param int $commentId
     * @return stdClass|null
     */
    public function correctPostIdAndCommentId(int $commentId): ?stdClass
    {
        $result = $this->pdo->prepare("SELECT comments.id, comments.post, comments.user FROM comments WHERE id=:id");
        $result->bindParam('id', $commentId, PDO::PARAM_INT);
        $result->execute();
        $result = $result->fetch();

        return ($result) ? : null;
    }
}
