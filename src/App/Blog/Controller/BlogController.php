<?php

namespace App\Blog\Controller;

use App\Blog\Model\CategoryModel;
use App\Blog\Model\CommentModel;
use App\Blog\Model\PostModel;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Post;
use Core\Controller\Controller;
use Core\Controller\ControllerInterface;
use Core\Form\BootstrapForm;

/**
 * Gère l'affichage des Posts
 *
 * Class BlogController
 * @package App\Blog\Controller
 */
class BlogController extends Controller implements ControllerInterface
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
     * @var CommentModel
     */
    protected $commentModel;

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
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \Exception
     */
    public function show(array $vars): void
    {
        $submitMessage = 'Valider';
        $textareaValue = null;
        $error = false;

        // Vérifie si le com à modifier existe sinon redirige la page vers le post simple
        if (isset($vars['commentId'])) {
            $submitMessage = 'Modifier';
            $textareaValue = $this->commentModel->find($vars['commentId'], Comment::class)->getComment();
            if (!$this->commentModel->idExist($vars['commentId'])) {
                $this->redirection('/post/' . $vars['id']);
            }
        }

        if (
            !empty($_POST) &&
            !empty($_POST['comment']) &&
            !empty($_POST['postId']) &&
            !empty($_POST['userId'])
        ) {
            // Vérifie si les hiddens correspondent aux autres infos (sécurité)
            if ($_POST['postId'] === $vars['id'] && $_SESSION['user']['id'] === $_POST['userId']) {
                if (!isset($vars['commentId'])) {
                    $this->commentModel->addComment($_POST['comment'], $_SESSION['user']['id'], $vars['id']);
                } elseif (isset($vars['commentId']) ) {
                    $this->commentModel->updateComment($_POST['comment'], $vars['commentId'], $_POST['userId'], $_POST['postId']);
                    $this->redirection('/post/' . $vars['id']);
                }
            } else {
                $error = true;
            }
        }

        $post = $this->postModel->findPostWithCategoryAndUser($vars['id']);

        /** @var Comment[] $comments */
        $comments = $this->commentModel->findAllByPost(
            $vars['id'],
            null,
            null,
            true,
            ' ORDER BY c.updatedAt DESC'
        );

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
