<?php

namespace App\Blog\Controller;

use App\Blog\Model\CommentModel;
use App\Controller\AppController;
use App\Entity\Comment;
use Core\Controller\ControllerInterface;

class CommentController extends AppController implements ControllerInterface
{
    /**
     * @var CommentModel
     */
    protected $commentModel;

    /**
     * Vérifie si
     * - l'id du com correspond à un id connu
     * - l'id de l'utilisateur correspond à celui stocké dans le com ou si l'utilisateur est admin
     * - l'id du post associé est le même que celui stocké dans le com
     * - l'utilisateur est connecté
     * Si ce n'est pas le cas renvoie vers l'adresse du post sans modification de commentaire
     * Il reçoit en référence le texte du bouton et tu textarea pour les modifier
     *
     * @param array $vars
     * @param string $submitMessage
     * @param string $textareaValue
     * @return void
     */
    public function pathUpdateComExistsAndIsCorrect(array $vars, string &$submitMessage, string &$textareaValue): void
    {
        if (isset($vars['commentId'])) {
            $submitMessage = 'Modifier';

            $ids = $this->commentModel->correctPostIdAndCommentId($vars['commentId']);

            if ($ids->id !== $vars['commentId'] || !(
                    $this->auth->isAdmin() ||
                    $ids->user !== $_SESSION['user']['id']
                ) ||
                $ids->post !== $vars['id'] ||
                !$this->auth->logged()
            ) {
                $this->redirection('/post/' . $vars['id']);
            }

            $textareaValue = $this->commentModel->find($vars['commentId'], Comment::class)->getComment();
        }
    }

    /**
     * Appelé par le PostController permet de modifier un commentaire
     *
     * @param array $vars
     * @param string $error
     */
    public function updateCom(array $vars, string &$error): void
    {
        if (!empty($_POST) &&
            !empty($_POST['comment']) &&
            !empty($_POST['postId']) &&
            !empty($_POST['userId'])
        ) {
            // Vérifie si les hiddens correspondent aux autres infos (sécurité)
            if ($_POST['postId'] === $vars['id'] && $_SESSION['user']['id'] === $_POST['userId']) {
                if (!isset($vars['commentId'])) {
                    $this->commentModel->addComment($_POST['comment'], $_SESSION['user']['id'], $vars['id']);
                } elseif (isset($vars['commentId'])) {
                    $this->commentModel->updateComment($_POST['comment'], $vars['commentId'], $_POST['userId'], $_POST['postId']);
                    $this->redirection('/post/' . $vars['id']);
                }
            } else {
                $error = true;
            }
        }
    }

    /**
     * Retourne la liste des commentaires d'un post
     *
     * @param int $idPost
     * @return array
     */
    public function listComByPost(int $idPost): array
    {
        return $this->commentModel->findAllByPost(
            $idPost,
            null,
            null,
            true,
            ' ORDER BY c.updatedAt DESC'
        );
    }
}
