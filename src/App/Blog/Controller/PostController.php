<?php

namespace App\Blog\Controller;

use App\Blog\Model\CategoryModel;
use App\Blog\Model\PostModel;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Post;
use Core\Controller\Controller;
use Core\Controller\ControllerInterface;
use Core\Form\BootstrapForm;

class PostController extends Controller implements ControllerInterface
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
     * Affiche l'ensemble des Posts selon la LIMIT
     *
     * @param array $vars
     * @return void
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

        $posts = $this->postModel->findAll(
            Post::class,
            $paginationOptions['start'],
            $paginationOptions['limit'],
            true,
            ' ORDER BY updatedAt DESC '
        );

        $categories = $this->categoryModel->findAll(Category::class);

        if ($paginationOptions['id'] <= $paginationOptions['pageNb']) {
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
     */
    public function show(array $vars): void
    {
        $submitMessage = 'Valider';
        $textareaValue = '';
        $error = false;

        $commentController = $this->container->get(CommentController::class);

        $commentController->pathUpdateComExistsAndIsCorrect($vars, $submitMessage, $textareaValue);

        $commentController->updateCom($vars, $error);

        $post = $this->postModel->findPostWithCategoryAndUser($vars['id']);

        /** @var Comment[] $comments */
        $comments = $commentController->listComByPost($vars['id']);

        $form = new BootstrapForm(' offset-sm-2 col-sm-8 loginForm');
        if (!$this->auth->logged()) {
            $form->item('<em>Vous devez être connecté·e pour laisser un commentaire : <a href="/user/login">se connecter</a>.</em>');
        } elseif ($error) {
            $form->item('<h4 class="error">Une erreur est survenue lors de l\'envoi du commentaire.</h4>');
        }
        $form->textarea('comment', 'Votre commentaire', 5, $textareaValue);
        $form->input('postId', '', $post->getId(), 'hidden');
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
     */
    public function category(array $vars): void
    {
        $nbPosts = $this->postModel->countPostByCategory($vars['slug']);

        $paginationOptions = $this->pagination($vars, $nbPosts);

        /** @var Post[] $posts */
        $posts = $this->postModel->findAllByCategory(
            $vars['slug'],
            $paginationOptions['start'],
            $paginationOptions['limit'],
            true,
            ' ORDER BY updatedAt DESC '
        );

        $categories = $this->categoryModel->findAll(Category::class);

        if ($paginationOptions['id'] <= $paginationOptions['pageNb']) {
            $this->render(
                'blog/category.twig',
                compact('posts', 'paginationOptions', 'categories')
            );
        } else {
            $this->render404();
        }
    }
}
