<?php

namespace App\Blog\Controller;

use App\Models\AdminModel;
use App\Models\UserModel;
use App\Models\CategoryModel;
use App\Models\CommentModel;
use App\Models\PostModel;
use App\Entities\Comment;
use App\Controller\AppController;
use App\Entities\User;
use App\various\AppHash;
use Core\Controller\ControllerInterface;
use Core\Form\BootstrapForm;
use DateTime;
use Exception;
use Jojotique\ORM\Classes\ORMController;
use Jojotique\ORM\Classes\ORMException;
use Jojotique\ORM\Classes\ORMTable;

class PostController extends AppController implements ControllerInterface
{
    /**
     * @var PostModel
     */
    protected $postModel;

    /**
     * @var CategoryModel
     */
    protected $categoryModel;

    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var AdminModel
     */
    protected $adminModel;

    /**
     * @var CommentModel
     */
    protected $commentModel;

    use AppHash;

    /**
     * Affiche l'ensemble des Posts selon la LIMIT
     *
     * @param array $vars
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws ORMException
     */
    public function index(array $vars): void
    {
        $nbPosts = $this->select
            ->select(['posts' => ['id']])->from('posts')
            ->countColumns()
            ->execute($this->postModel);

        $paginationOptions = $this->pagination($vars, $nbPosts);

        $this->paginationMax($paginationOptions, __ROOT__ . '/blog/');

        $posts = $this->select->select([
            'posts' => ['id', 'title', 'content', 'createdAt', 'updatedAt']
        ])->from('posts')
            ->limit($paginationOptions['limit'], $paginationOptions['start'])
            ->orderBy(['updatedAt' => 'desc'])
            ->execute($this->postModel);

        $categories = $this->findCategories();

        if ($paginationOptions['id'] <= $paginationOptions['pageNb']) {
            $this->render('blog/index.twig', compact('posts', 'paginationOptions', 'categories'));
        } else {
            $this->redirection(__ROOT__ . '/blog/' . $paginationOptions['pageNb']);
        }
    }

    /**
     * Affiche un Post particulier
     *
     * @param array $vars
     * @return void
     * @throws ORMException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function show(array $vars): void
    {
        $userConnected = $this->findUserConnected();

        $submitMessage = 'Valider';
        $error = false;
        $noComment = false;

        try {
            $post = $this->select->select([
                'posts'      => ['id', 'title', 'content', 'createdAt', 'updatedAt'],
                'categories' => ['id', 'name', 'slug'],
                'users'      => ['id', 'pseudo']
            ])->from('posts')
                ->innerJoin('categories', ['posts.category' => 'categories.id'])
                ->innerJoin('users', ['posts.user' => 'users.id'])
                ->where(['posts.id' => $vars['id']])
                ->insertEntity(['categories' => 'posts'], ['id' => 'category'], 'oneToOne')
                ->insertEntity(['users' => 'posts'], ['id' => 'user'], 'oneToOne')
                ->singleItem()
                ->execute($this->postModel, $this->categoryModel, $this->userModel);
        } catch (ORMException $e) {
            $this->redirection(__ROOT__ . '/blog/1');
        }

        if (isset($_POST['comment'])) {
            try {
                $this->updateComment($vars['id'], $userConnected, $vars['commentId']);
            } catch (Exception $e) {
            }
        }

        $comments = $this->allComments($noComment, $vars['id']);

        if (!empty($comments)) {
            $tokens = [];
            foreach ($comments as $comment) {
                $code1 = strlen($comment->id);
                $code2 = strlen($comment->user->pseudo);
                $code3 = strlen($post->id);

                $tokens[$comment->id] = $this->appHash(
                    $code3 . $comment->id . $code1 . $post->id . $code2 . $comment->user->pseudo
                );
            }
        }

        $errorDeleteComment = null;
        if (isset($vars['deletedCommentId'])) {
            try {
                $this->deleteComment($vars['id'], $vars['deletedCommentId'], $userConnected);
            } catch (Exception $e) {
                $errorDeleteComment = $e->getMessage();
            }
        }

        $commentUpdated = new Comment();
        if (!empty($vars['commentId'])) {
            $commentUpdated = $this->findCommentUpdated($vars['commentId'], $vars['id']);

            if (is_null($commentUpdated)) {
                $this->redirection(__ROOT__ . '/post/' . $vars['id']);
            }
        }

        //Form
        $form = new BootstrapForm(' offset-sm-2 col-sm-8 loginForm');
        if ($error) {
            $form->item('<h4 class="error">Une erreur est survenue lors de l\'envoi du commentaire.</h4>');
        } elseif (!is_null($errorDeleteComment)) {
            $form->item("<h4 class='error'>{$errorDeleteComment}</h4>");
        }
        $form->textarea('comment', 'Votre commentaire', 5, $commentUpdated->comment);
        if (isset($_POST['token'])) {
            $form->input('token', '', $_POST['token'], 'hidden');
        }
        $form = $form->submit($submitMessage);

        $this->render(
            'blog/show.twig',
            compact('post', 'comments', 'form', 'noComment', 'userConnected', 'tokens')
        );
    }

    /**
     * Affiche les articles triés selon la catégorie
     *
     * @param array $vars
     * @return void
     * @throws ORMException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function category(array $vars): void
    {
        $nbPosts = $this->postModel->countPostByCategory($vars['slug']);

        $paginationOptions = $this->pagination($vars, $nbPosts);

        $this->paginationMax($paginationOptions, "/p5/categorie/{$vars['slug']}-");

        $posts = $this->select->select([
            'posts'      => ['id', 'title', 'content', 'createdAt', 'updatedAt', 'user'],
            'categories' => ['name', 'slug']
        ])->from('posts')
            ->innerJoin('categories', ['posts.category' => 'categories.id'])
            ->where(['categories.slug' => $vars['slug']])
            ->orderBy(['posts.updatedAt' => 'desc'])
            ->limit($paginationOptions['limit'], $paginationOptions['start'])
            ->insertEntity(['categories' => 'posts'], ['id' => 'category'], 'oneToMany')
            ->execute($this->postModel, $this->categoryModel, $this->container->get(UserModel::class));

        $categories = $this->findCategories();

        if ($paginationOptions['id'] <= $paginationOptions['pageNb']) {
            $this->render(
                'blog/category.twig',
                compact('posts', 'paginationOptions', 'categories')
            );
        } else {
            $this->redirection(__ROOT__ . '/categories/' . $paginationOptions['pageNb']);
        }
    }

    /**
     * Affiche les articles triés selon le pseudo de l'utilisateur
     *
     * @param array $vars
     * @return void
     * @throws ORMException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function author(array $vars): void
    {
        $user = $this->select->from('users')
            ->select(['users' => ['id']])
            ->where(['pseudo' => $vars['pseudo']])
            ->singleItem()
            ->execute($this->userModel);

        $nbPosts = $this->postModel->countPostsByUser($user->id);

        $paginationOptions = $this->pagination($vars, $nbPosts);

        $this->paginationMax($paginationOptions, "/p5/auteur/{$vars['pseudo']}-");

        $posts = $this->select->select([
            'posts' => ['id', 'title', 'content', 'createdAt', 'updatedAt', 'user'],
            'users' => ['id', 'pseudo']
        ])->from('posts')
            ->innerJoin('users', ['posts.user' => 'users.id'])
            ->where(['users.pseudo' => $vars['pseudo']])
            ->orderBy(['posts.updatedAt' => 'desc'])
            ->limit($paginationOptions['limit'], $paginationOptions['start'])
            ->insertEntity(['users' => 'posts'], ['id' => 'user'], 'oneToMany')
            ->execute($this->postModel, $this->categoryModel, $this->userModel);

        $categories = $this->findCategories();

        if ($paginationOptions['id'] <= $paginationOptions['pageNb']) {
            $this->render(
                'blog/author.twig',
                compact('posts', 'paginationOptions', 'categories')
            );
        } else {
            $this->redirection(__ROOT__ . '/author/' . $paginationOptions['pageNb']);
        }
    }

    /**
     * @param bool $noComment
     * @param int $postId
     * @return Comment[]|null
     */
    private function allComments(bool &$noComment, int $postId): ?array
    {
        $comments = null;

        try {
            $comments = $this->select->select([
                'comments' => ['id', 'comment', 'updatedAt', 'createdAt', 'user', 'post'],
                'users'    => ['id', 'pseudo', 'firstName', 'lastName', 'mail', 'phone', 'password', 'admin'],
                'admin'    => ['id', 'name']
            ])->from('comments')
                ->innerJoin('users', ['users.id' => 'comments.user'])
                ->innerJoin('admin', ['admin.id' => 'users.admin'])
                ->insertEntity(['admin' => 'users'], ['id' => 'admin'], 'manyToMany')
                ->insertEntity(['users' => 'comments'], ['id' => 'user'], 'manyToMany')
                ->where(['comments.post' => $postId])
                ->orderBy(['comments.createdAt' => 'desc'])
                ->execute($this->commentModel, $this->userModel, $this->adminModel);
        } catch (ORMException $e) {
            if ($e->getCode() === ORMException::NO_ELEMENT) {
                $noComment = true;
            }
        }

        return $comments;
    }

    /**
     * @param int $commentId
     * @param int $postId
     * @return Comment|null
     */
    private function findCommentUpdated(int $commentId, int $postId): ?Comment
    {
        try {
            /** @var Comment|null $comment */
            $comment = $this->select->select([
                'comments' => ['id', 'comment', 'post']
            ])->from('comments')
                ->where(['id' => $commentId])
                ->singleItem()
                ->execute($this->commentModel);
        } catch (ORMException $e) {
            if ($e->getCode() === ORMException::NO_ELEMENT) {
                $comment = null;
            }
        }

        if ($postId !== $comment->postId) {
            $comment = null;
        }

        return $comment;
    }

    /**
     * Vérifie si un "token" est envoyé :
     * --> Si oui, c'est que c'est une modification de commentaire. Vérifie si le "token" est valide si oui modifie
     * --> Si non, c'est un ajout
     * --> Sinon renvoie une erreur.
     *
     * @param int $postId
     * @param User $user
     * @param int|null $commentId
     * @throws Exception
     * @throws ORMException
     */
    private function updateComment(int $postId, User $user, ?int $commentId = null)
    {
        if (!empty($_POST) && isset($_POST['comment']) && isset($_POST['token'])) {
            $ormTable = new ORMTable('comments');
            $ormTable->constructWithStdclass($this->commentModel->ORMShowColumns());

            $comment = new Comment($ormTable, true);
            $comment->id = $commentId;
            $comment->comment = $_POST['comment'];

            $originalComment = $this->select->select(['comments' => ['id', 'createdAt', 'user', 'post']])
                ->from('comments')
                ->singleItem()
                ->where(['id' => $commentId])
                ->execute($this->commentModel);
            $comment->createdAt = $originalComment->createdAt;
            $comment->updatedAt = new DateTime();
            $comment->postId = $originalComment->postId;
            $comment->userId = $originalComment->userId;

            $comment->setPrimaryKey(['id']);

            $code1 = strlen($comment->id);
            $code2 = strlen($user->pseudo);
            $code3 = strlen($postId);
            $token = $this->appHash($code3 . $comment->id . $code1 . $postId . $code2 . $user->pseudo);

            if ($token === $_POST['token']) {
                $ormController = new ORMController();
                $ormController->save($comment, $this->commentModel);

                $this->redirection(__ROOT__ . '/post/' . $postId);
            } else {
                throw new Exception("Une erreur est survenue lors de l'enregistrement du commentaire,
                    veuillez réessayer.");
            }
        } elseif (!empty($_POST) && isset($_POST['comment'])) {
            $ormTable = new ORMTable('comments');
            $ormTable->constructWithStdclass($this->commentModel->ORMShowColumns());

            $comment = new Comment($ormTable, true);
            $comment->comment = $_POST['comment'];
            $comment->createdAt = $comment->updatedAt = new DateTime();
            $comment->postId = $postId;
            $comment->userId = $user->id;

            $ormController = new ORMController();
            $ormController->save($comment, $this->commentModel);
        } else {
            throw new Exception("Une erreur est survenue lors de l'envoi du commentaire. Veuillez réessayer.");
        }
    }

    /**
     * @param int $postId
     * @param int $deletedCommentId
     * @param User $user
     * @throws Exception
     * @throws ORMException
     */
    private function deleteComment(int $postId, int $deletedCommentId, User $user)
    {
        if (!empty($_POST) && isset($_POST['token'])) {
            $code1 = strlen($deletedCommentId);
            $code2 = strlen($user->pseudo);
            $code3 = strlen($postId);
            $token = $this->appHash($code3 . $deletedCommentId . $code1 . $postId . $code2 . $user->pseudo);

            if ($token === $_POST['token']) {
                $comment = $this->select->select(['comments' => ['id']])
                    ->from('comments')
                    ->where(['id' => $deletedCommentId])
                    ->singleItem()
                    ->execute($this->commentModel);
                $ormController = new ORMController();
                $ormController->delete($comment, $this->commentModel);
                $this->redirection(__ROOT__ . '/post/' . $postId);
            } elseif ($this->auth->isAdmin($user)) {
                $comment = $this->select->select(['comments' => ['id', 'post']])
                    ->singleItem()
                    ->where(['id' => $deletedCommentId])
                    ->from('comments')
                    ->execute($this->commentModel);

                if ($comment->postId === $postId) {
                    $ormController = new ORMController();
                    $ormController->delete($comment, $this->commentModel);
                    $this->redirection(__ROOT__ . '/post/' . $postId);
                } else {
                    throw new Exception("Une erreur est survenue lors de la suppression du commentaire.");
                }
            } else {
                throw new Exception("Vos droits ne vous permettent pas de supprimer ce commentaire.");
            }
        } else {
            $this->redirection(__ROOT__ . '/post/' . $postId);
        }
    }
}
