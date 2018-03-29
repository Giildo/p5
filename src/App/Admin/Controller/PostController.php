<?php

namespace App\Admin\Controller;

use App\Controller\AppController;
use App\Entities\Category;
use App\Entities\Post;
use App\Models\AdminModel;
use App\Models\CategoryModel;
use App\Models\PostModel;
use App\Models\UserModel;
use Core\Controller\ControllerInterface;
use Core\Form\BootstrapForm;
use DateTime;
use Exception;
use Jojotique\ORM\Classes\ORMController;
use Jojotique\ORM\Classes\ORMEntity;
use Jojotique\ORM\Classes\ORMException;
use Jojotique\ORM\Classes\ORMTable;
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
     * @throws Exception
     */
    public function index(array $vars): void
    {
        $user = $this->findUserConnected();

        if ($this->auth->isAdmin($user)) {
            $nbPage = $this->postModel->count();
        } else {
            $nbPage = $this->postModel->countPostsByUser($user->id);
        }

        $paginationOptions = $this->pagination($vars, $nbPage, 'admin.limit.post');

        $this->paginationMax($paginationOptions, '/admin/posts/');

        try {
            if ($this->auth->isAdmin($user)) {
                $posts = $this->findPostsWithCategoryAndUser(
                    [],
                    $paginationOptions['limit'],
                    $paginationOptions['start']
                );
            } else {
                $posts = $this->findPostsWithCategoryAndUser(
                    ['users.id' => $user->id],
                    $paginationOptions['limit'],
                    $paginationOptions['start']
                );
            }
        } catch (ORMException $e) {
            $this->redirection('/admin/accueil');
        }

        $formCode = [];
        $code1 = strlen($user->pseudo);
        foreach ($posts as $post) {
            $code2 = strlen($post->title);
            $token = $this->auth->appHash($code2 . $post->title . $user->pseudo . $code1);
            $formCode[$post->id] = $token;
        }

        $this->render('admin/posts/index.twig', compact('posts', 'paginationOptions', 'user', 'formCode', 'vars'));
    }

    /**
     * @param array $vars
     * @return void
     * @throws ORMException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
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

        if ($this->auth->isAdmin($this->findUserConnected())) {
            $form->select('user', $usersSelect, $post->user->pseudo, 'Auteur de l\'article');
        } else {
            $form->item("<p>Auteur : {$post->user->pseudo}</p>");
            $form->input('user', '', $post->user->pseudo, 'hidden');
        }

        $form = $form->submit('Valider');

        $this->render('admin/posts/update.twig', compact('form', 'post'));
    }

    /**
     * @throws ORMException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function add(): void
    {
        if ($this->auth->logged($this->findUserConnected())) {
            $u_error = false;
            $u_success = false;
            $errorMessage = '';
            $category = new Category();
            $post = new Post();

            if (!empty($_POST) &&
                isset($_POST['title']) &&
                isset($_POST['content']) &&
                isset($_POST['category'])
            ) {
                try {
                    $post = $this->addPost();
                } catch (Exception $e) {
                    $u_error = true;
                    $errorMessage = $e->getMessage();
                }

                if (!$u_error) {
                    $u_success = true;
                }
            }

            $categories = $this->select->select(['categories' => ['name']])
                ->from('categories')
                ->execute($this->categoryModel);

            $categoriesSelect = $this->createSelectOptions($categories, 'name');

            if (!is_null($post->categoryId)) {
                $category = $this->select->select(['categories' => ['name']])
                    ->from('categories')
                    ->singleItem()
                    ->where(['id' => $post->categoryId])
                    ->execute($this->categoryModel);
            }

            $form = new BootstrapForm(' offset-sm-2 col-sm-8 loginForm');

            if ($u_success) {
                $form->item("<h4 class='success'>Ajout réussie !</h4>");
            } elseif ($u_error) {
                $form->item("<h4 class='error'>{$errorMessage}</h4>");
            }

            $form->input('title', 'Titre de l\'article', $post->title);
            $form->textarea('content', 'Contenu de l\'article', 10, $post->content);
            $form->select('category', $categoriesSelect, $category->name, 'Catégorie associée');

            $form = $form->submit('Valider');

            $this->render('/admin/posts/add.twig', compact('form'));
        } else {
            $this->renderNotLog();
        }
    }

    /**
     * @throws ORMException
     * @throws Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function delete(): void
    {
        $userConnected = $this->findUserConnected();

        if ($this->auth->logged($userConnected)) {
            if (!empty($_POST) && isset($_POST['token']) && isset($_POST['id'])) {
                $post = $this->select->select(['posts' => ['id', 'title']])
                    ->from('posts')
                    ->where(['id' => $_POST['id']])
                    ->singleItem()
                    ->execute($this->postModel);

                $code1 = strlen($userConnected->pseudo);
                $code2 = strlen($post->title);
                $token = $this->auth->appHash($code2 . $post->title . $userConnected->pseudo . $code1);

                if ($token === $_POST['token']) {
                    (new ORMController())->delete($post, $this->postModel);

                    /*Renvoie vers la page d'administration des posts soit
                    --> à la page en cours s'il est envoyé en POST
                    --> sinon vers la 1ère page*/
                    $vars = [];
                    $vars['id'] = (isset($_POST['indexId'])) ? $_POST['id'] : '1';
                    $this->index($vars);
                } else {
                    $this->render('admin/categories/index.twig', []);
                    throw new Exception("Une erreur est survenue lors de la suppression de la catégorie,
                        veuillez réessayer.");
                }
            } else {
                $this->render('admin/categories/index.twig', []);
                throw new Exception("Une erreur est survenue lors de la suppression de la catégorie,
                    veuillez réessayer.");
            }
        } else {
            $this->renderNotLog();
        }
    }

    /**
     * @param array|null $where
     * @param int|null $limit
     * @param int|null $start
     * @param bool|null $singleItem
     * @return ORMEntity[]|ORMEntity
     * @throws ORMException
     */
    private function findPostsWithCategoryAndUser(
        ?array $where = [],
        ?int $limit = null,
        ?int $start = null,
        ?bool $singleItem = false
    ) {
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
            ->orderBy(['posts.updatedAt' => 'desc'])
            ->where($where)
            ->singleItem($singleItem)
            ->execute($this->postModel, $this->categoryModel, $this->userModel);
    }

    /**
     * @param string $id
     * @return void
     * @throws ORMException
     */
    private function updatePost(string $id): void
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

        (new ORMController())->save($post, $this->postModel);
    }

    /**
     * @return Post
     * @throws ORMException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function addPost(): Post
    {
        if (empty($_POST['title'])) {
            throw new ORMException('Le champ "Titre de l\'article" doit être renseigné !');
        }
        if (empty($_POST['content'])) {
            throw new ORMException('Le champ "Contenu de l\'article" doit être renseigné !');
        }
        if (empty($_POST['category'])) {
            throw new ORMException('Le champ "Auteur de l\'article" doit être renseigné !');
        }

        $ormTable = new ORMTable('posts');
        $ormTable->constructWithStdclass($this->postModel->ORMShowColumns());

        $post = new Post($ormTable, true);
        $post->title = $_POST['title'];
        $post->content = $_POST['content'];
        $post->createdAt = $post->updatedAt = new DateTime('now');
        $post->userId = $this->findUserConnected()->id;

        $category = $this->select->select(['categories' => ['id']])
            ->from('categories')
            ->where(['name' => $_POST['category']])
            ->singleItem()
            ->execute($this->categoryModel);
        $post->categoryId = $category->id;
        $post->setPrimaryKey(['id']);

        (new ORMController())->save($post, $this->postModel);

        return $post;
    }
}
