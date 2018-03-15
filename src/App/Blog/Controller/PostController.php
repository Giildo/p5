<?php

namespace App\Blog\Controller;

use App\Admin\Model\UserModel;
use App\Blog\Model\CategoryModel;
use App\Blog\Model\PostModel;
use App\Entity\Comment;
use App\Controller\AppController;
use Core\Controller\ControllerInterface;
use Core\Form\BootstrapForm;
use Core\ORM\Classes\ORMEntity;

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
     * Affiche l'ensemble des Posts selon la LIMIT
     *
     * @param array $vars
     * @return void
     * @throws \Core\ORM\Classes\ORMException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index(array $vars): void
    {
        $nbPosts = $this->postModel->count();

        $paginationOptions = $this->pagination($vars, $nbPosts);

        $posts = $this->select->select([
            'posts' => ['id', 'title', 'content', 'createdAt', 'updatedAt']
        ])->from('posts')
            ->limit($paginationOptions['limit'], $paginationOptions['start'])
            ->orderBy(['updatedAt' => 'desc'])
            ->execute($this->postModel);

        $categories = $this->findCategories();

        if ($paginationOptions['id'] <= $paginationOptions['pageNb'] ) {
            $this->render('blog/index.twig', compact('posts', 'paginationOptions', 'categories'));
        } else {
            $this->render404();
        }
    }

    /**
     * Affiche un Post particulier
     *
     * @param array $vars
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \Core\ORM\Classes\ORMException
     */
    public function show(array $vars): void
    {
        $submitMessage = 'Valider';
        $textareaValue = '';
        $error = false;

        $commentController = $this->container->get(CommentController::class);

        $commentController->pathUpdateComExistsAndIsCorrect($vars, $submitMessage, $textareaValue);

        $commentController->updateCom($vars, $error);

        $post = $this->select->select([
            'posts' => ['id', 'title', 'content', 'createdAt', 'updatedAt'],
            'categories' => ['id', 'name', 'slug'],
            'users' => ['id', 'pseudo']
        ])->from('posts')
            ->innerJoin('categories', ['posts.category' => 'categories.id'])
            ->innerJoin('users', ['posts.user' => 'users.id'])
            ->where(['posts.id' => $vars['id']])
            ->insertEntity(['categories' => 'posts'], ['id' => 'category'], 'oneToOne')
            ->insertEntity(['users' => 'posts'], ['id' => 'user'], 'oneToOne')
            ->singleItem()
            ->execute($this->postModel, $this->categoryModel, $this->userModel);

        /** @var Comment[] $comments */
        $comments = $commentController->listComByPost($vars['id']);

        $form = new BootstrapForm(' offset-sm-2 col-sm-8 loginForm');
        if (!$this->auth->logged()) {
            $form->item('<em>Vous devez être connecté·e pour laisser un commentaire : <a href="/user/login">se connecter</a>.</em>');
        } elseif ($error) {
            $form->item('<h4 class="error">Une erreur est survenue lors de l\'envoi du commentaire.</h4>');
        }
        $form->textarea('comment', 'Votre commentaire', 5, $textareaValue);
        $form->input('postId', '', $post->id, 'hidden');
        if ($this->auth->logged()) {
            $form->input('userId', '', $_SESSION['user']['id'], 'hidden');
        }
        $form = $form->submit($submitMessage);

        if ($post) {
            $this->render('blog/show.twig', compact('post', 'comments', 'form'));
        } else {
            $this->render404();
        }
    }

    /**
     * Affiche les articles triés selon la catégorie
     *
     * @param array $vars
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \Core\ORM\Classes\ORMException
     */
    public function category(array $vars): void
    {
        $nbPosts = $this->postModel->countPostByCategory($vars['slug']);

        $paginationOptions = $this->pagination($vars, $nbPosts);

        $posts = $this->select->select([
            'posts' => ['id', 'title', 'content', 'createdAt', 'updatedAt', 'user'],
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
            $this->render404();
        }
    }

    /**
     * Affiche les articles triés selon le pseudo de l'utilisateur
     *
     * @param array $vars
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \Core\ORM\Classes\ORMException
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
            $this->render404();
        }
    }
}
