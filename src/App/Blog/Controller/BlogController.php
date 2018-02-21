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
        if (!empty($_POST) &&
            isset($_POST['comment']) &&
            isset($_POST['postId']) &&
            isset($_POST['userId'])
        ) {
            $this->commentModel->addComment($_POST['comment'], $_POST['userId'], $_POST['postId']);
        }

        $post = $this->postModel->find($vars['id'], Post::class);
        $category = $this->categoryModel->find($post->getCategory(), Category::class);

        /** @var Comment[] $comments */
        $comments = $this->commentModel->findAllByPost($vars['id'], null, null, true, ' ORDER BY c.updatedAt DESC');

        $form = new BootstrapForm(' offset-sm-2 col-sm-8');
        if (!$this->auth->logged()) {
            $form->item('<em>Vous devez être connecté·e pour laisser un commentaire : <a href="/user/login">se connecter</a>.</em>');
        }
        $form->textarea('comment','Votre commentaire');
        $form->input('postId', '', $post->getId(), 'hidden');
        if ($this->auth->logged()) {
            $form->input('userId', '', $_SESSION['user']['id'], 'hidden');
        }
        $form = $form->submit('Valider');

        if ($post) {
            $this->render('blog/show.twig', compact('post', 'category', 'comments', 'form'));
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
        $categoryId = $this->categoryModel->findBySlug($vars['slug']);

        $nbPosts = $this->postModel->count('category', $categoryId);

        $paginationOptions = $this->pagination($vars, $nbPosts);

        /** @var Post[] $posts */
        $posts = $this->postModel->findAllByCategory(
            $categoryId, $paginationOptions['start'], $paginationOptions['limit'], true, ' ORDER BY updatedAt DESC '
        );

        $categories = $this->categoryModel->findAll(Category::class);

        $categoryName = $posts[0]->getCategory();

        if ($paginationOptions['id'] <= $paginationOptions['pageNb']) {
            $this->render(
                'blog/category.twig',
                compact('posts', 'paginationOptions', 'categories', 'categoryName', 'vars')
            );
        } else {
            $this->render404();
        }
    }
}
