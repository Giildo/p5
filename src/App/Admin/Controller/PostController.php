<?php

namespace App\Admin\Controller;

use App\Admin\Model\AdminModel;
use App\Admin\Model\UserModel;
use App\Blog\Model\CategoryModel;
use App\Blog\Model\PostModel;
use App\Controller\AppController;
use App\Entity\Post;
use Core\Controller\ControllerInterface;
use Core\Form\BootstrapForm;
use Core\ORM\Classes\ORMController;
use Core\ORM\Classes\ORMEntity;
use Core\ORM\Classes\ORMException;
use Core\ORM\Classes\ORMTable;
use DateTime;
use stdClass;

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
        $user = $_SESSION['user'];

        if ($this->auth->isAdmin()) {
            $nbPage = $this->postModel->count();
        } else {
            $nbPage = $this->postModel->countPostsByUser($_SESSION['user']->id);
        }

        $paginationOptions = $this->pagination($vars, $nbPage, 'admin.limit.post');

        if ($this->auth->isAdmin()) {
            $posts = $this->findPostsWithCategoryAndUser([], $paginationOptions['limit'], $paginationOptions['start']);
        } else {
            $posts = $this->findPostsWithCategoryAndUser(['users.id' => $user->id], $paginationOptions['limit'], $paginationOptions['start']);
        }

        $this->render('admin/posts/index.twig', compact('posts', 'paginationOptions', 'user'));
    }

    /**
     * @param array $vars
     * @return void
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws ORMException
     */
    public function update(array $vars):void
    {
        $u_success = false;
        $u_error = false;
        $errorMessage = '';

        if (!empty($_POST) &&
            isset($_POST['title']) &&
            isset($_POST['content']) &&
            isset($_POST['user']) &&
            isset($_POST['category'])
        ) {
            try {
                $this->updatePost($vars['id']);
            } catch (ORMException $e) {
                $u_error = true;
                $errorMessage = $e->getMessage();
            }

            if (!$u_error) {
                $u_success = true;
            }
        }

        $post = $this->findPostsWithCategoryAndUser(['posts.id' => $vars['id']], null, null, true);
        $categories = $this->findCategories();
        $users = $this->select->select([
            'users' => ['id', 'pseudo', 'admin'],
            'admin' => ['id', 'name']
        ])->from('users')
            ->innerJoin('admin', ['users.admin' => 'admin.id'])
            ->where(['admin.id' => ['1', '2']], true)
            ->insertEntity(['admin' => 'users'], ['id' => 'admin'], 'manyToMany')
            ->execute($this->userModel, $this->adminModel);

        $categoriesSelect = $this->createSelectOptions($categories, 'name');
        $usersSelect = $this->createSelectOptions($users, 'pseudo');

        $form = new BootstrapForm(' offset-sm-2 col-sm-8 loginForm');

        if ($u_success) {
            $form->item("<h4 class='success'>Modification réussie !</h4>");
        } elseif ($u_error) {
            $form->item("<h4 class='error'>{$errorMessage}</h4>");
        }

        $form->input('title', 'Titre de l\'article', $post->title);
        $form->textarea('content', 'Contenu de l\'article', 10, $post->content);
        $form->select('category', $categoriesSelect, $post->category->name, 'Catégorie associée');

        if ($this->auth->isAdmin()) {
            $form->select('user', $usersSelect, $post->user->pseudo, 'Auteur de l\'article');
        } else {
            $form->item("<p>Auteur : {$post->user->pseudo}</p>");
            $form->input('user', '', $post->user->pseudo, 'hidden');
        }

        $form = $form->submit('Valider');

        $this->render('admin/posts/update.twig', compact('form', 'post'));
    }

    /**
     * @param array|null $where
     * @param int|null $limit
     * @param int|null $start
     * @param bool|null $singleItem
     * @return ORMEntity[]|ORMEntity
     * @throws ORMException
     */
    private function findPostsWithCategoryAndUser(?array $where = [], ?int $limit = null, ?int $start = null, ?bool $singleItem = false)
    {
        return $this->select->select([
            'posts'      => ['id', 'title', 'content', 'createdAt', 'updatedAt', 'user', 'category'],
            'categories' => ['id', 'name'],
            'users'      => ['id', 'pseudo']
        ])->from('posts')
            ->innerJoin('categories', ['categories.id' => 'posts.category'])
            ->innerJoin('users', ['users.id' => 'posts.user'])
            ->insertEntity(['users' => 'posts'], ['id' => 'user'], 'manyToMany')
            ->insertEntity(['categories' => 'posts'], ['id' => 'category'], 'manyToMany')
            ->limit($limit, $start)
            ->orderBy(['updatedAt' => 'desc'])
            ->where($where)
            ->singleItem($singleItem)
            ->execute($this->postModel, $this->categoryModel, $this->userModel);
    }

    /**
     * @param string $id
     * @throws ORMException
     */
    private function updatePost(string $id)
    {
        if (empty($_POST['title'])) {
            throw new ORMException("Le champ \"Titre de l'article\" doit être renseigné !");
        }
        if (empty($_POST['content'])) {
            throw new ORMException("Le champ \"Contenu de l'article\" doit être renseigné !");
        }
        if (empty($_POST['user'])) {
            throw new ORMException("Le champ \"Catégorie associée\" doit être renseigné !");
        }
        if (empty($_POST['category'])) {
            throw new ORMException("Le champ \"Auteur de l'article\" doit être renseigné !");
        }

        $ormTable = new ORMTable('posts');
        $ormTable->constructWithStdclass($this->postModel->ORMShowColumns());

        $originalPost = $this->select->select(['posts' => ['createdAt']])
            ->from('posts')
            ->where(['id' => $id])
            ->singleItem()
            ->execute($this->postModel);

        $post = new Post($ormTable, true);
        $stdClass = new stdClass();
        $post->constructWithStdclass($stdClass);
        $post->id = $id;
        $post->title = $_POST['title'];
        $post->content = $_POST['content'];
        $post->createdAt = $originalPost->createdAt;
        $post->updatedAt = new DateTime('now');

        $user = $this->select->select(['users' => ['id']])
            ->from('users')
            ->where(['pseudo' => $_POST['user']])
            ->singleItem()
            ->execute($this->userModel);
        $post->userId = $user->id;

        $category = $this->select->select(['categories' => ['id']])
            ->from('categories')
            ->where(['name' => $_POST['category']])
            ->singleItem()
            ->execute($this->categoryModel);
        $post->categoryId = $category->id;
        $post->setPrimaryKey(['id']);

        $ormController = new ORMController();
        $ormController->save($post, $this->postModel);
    }
}
