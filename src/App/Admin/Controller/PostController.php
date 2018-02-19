<?php

namespace App\Admin\Controller;

use App\Admin\Model\UserModel;
use App\Blog\Model\CategoryModel;
use App\Blog\Model\PostModel;
use App\Entity\Category;
use App\Entity\User;
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
     * @var UserModel
     */
    protected $userModel;

    /**
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
        $admin = false;

        if ($this->auth->isAdmin()) {
            $admin = true;
            $nbPage = $this->postModel->count();
        } else {
            $nbPage = $this->postModel->count('user', $_SESSION['user']['id']);
        }

        $paginationOptions = $this->pagination($vars, $nbPage, 'admin.limit.post');

        $posts = $this->postModel->findAllByUserAndCategory(
            $_SESSION['user']['id'],
            $paginationOptions['start'],
            $paginationOptions['limit'],
            true,
            'ORDER BY updatedAt DESC',
            $admin
        );

        $this->render('admin/posts/index.twig', compact('posts', 'paginationOptions'));
    }

    /**
     * @param array $vars
     * @return void
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function update(array $vars):void
    {
        if (!empty($_POST) &&
            isset($_POST['name']) &&
            isset($_POST['content']) &&
            isset($_POST['user']) &&
            isset($_POST['category'])
        ) {
            $user = $this->userModel->findIdByColumn('pseudo', $_POST['user'], User::class);
            $category = $this->categoryModel->findIdByColumn('name', $_POST['category'], Category::class);
            $this->postModel->updatePost($user->getId(), $category->getId(), $_POST, $vars['id']);
        }
        $post = $this->postModel->findPostWithCategoryAndUser($vars['id']);
        $categories = $this->categoryModel->findAll(Category::class);
        $users = $this->userModel->findAll(User::class);

        $categoriesSelect = $this->createSelectOptions($categories, 'getName');
        $usersSelect = $this->createSelectOptions($users, 'getPseudo');

        $keys = ['name', 'content', 'user', 'category'];
        $posts = $this->createPost($keys);
        $posts = $this->createPostWithEntity($keys, $posts, $post);

        $form = new BootstrapForm(' offset-sm-2 col-sm-8 loginForm');
        $form->input('name', 'Titre de l\'article', $posts['name']);
        $form->textarea('content', 'Contenu de l\'article', 10, $posts['content']);
        $form->select('category', $categoriesSelect, $posts['category'], 'Catégorie associée');

        if ($this->auth->isAdmin()) {
            $form->select('user', $usersSelect, $posts['user'], 'Auteur de l\'article');
        } else {
            $form->item("<p>Auteur : {$post->getUser()}</p>");
        }

        $form = $form->submit('Valider');

        $this->render('admin/posts/update.twig', compact('form', 'post'));
    }
}
