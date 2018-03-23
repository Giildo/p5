<?php

namespace App\Blog\Controller;

use App\Admin\Model\AdminModel;
use App\Admin\Model\UserModel;
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
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var AdminModel
     */
    protected $adminModel;


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

            $user = $_SESSION['user'];

            if ($ids->id !== $vars['commentId'] || !(
                    $this->auth->isAdmin($user) ||
                    $ids->user !== $user->id
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
     * @throws \Core\ORM\Classes\ORMException
     */
    public function listComByPost(int $idPost): array
    {
        var_dump($this->select);
        die();
        $this->select->select([
            'comments' => ['id', 'comment', 'updatedAt', 'createdAt', 'user', 'post'],
            'users'    => ['id', 'pseudo', 'firstName', 'lastName', 'mail', 'phone', 'password', 'admin'],
            'admin'    => ['id', 'name']
        ])->from('comments')
            ->innerJoin('users', ['users.id' => 'comments.user'])
            ->innerJoin('admin', ['admin.id' => 'users.admin'])
            ->insertEntity(['admin' => 'users'], ['id' => 'admin'], 'manyToMany')
            ->insertEntity(['users' => 'comments'], ['id' => 'user'], 'manyToMany')
            ->where(['comments.post' => $idPost])
            ->orderBy(['comments.updatedAt' => 'desc'])
            ->execute($this->commentModel, $this->userModel, $this->adminModel);
    }
}
