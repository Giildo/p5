<?php

namespace App\Admin\Controller;

use App\Blog\Model\CategoryModel;
use App\Controller\AppController;
use App\Entity\Category;
use Core\Controller\ControllerInterface;
use Core\Form\BootstrapForm;
use Core\ORM\Classes\ORMController;
use Core\ORM\Classes\ORMException;
use Core\ORM\Classes\ORMTable;

class CategoryController extends AppController implements ControllerInterface
{
    /**
     * @var CategoryModel
     */
    protected $categoryModel;

    /**
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
        $user = $_SESSION['user'];

        if ($this->auth->isAdmin($user)) {
            $nbPage = $this->categoryModel->count();

            $paginationOptions = $this->pagination($vars, $nbPage, 'admin.limit.category');

            $this->paginationMax($paginationOptions, '/admin/categories/');

            $categories = $this->findCategories();

            $formCode = [];
            $code1 = strlen($user->pseudo);
            foreach ($categories as $category) {
                $code2 = strlen($category->slug);
                $token = $this->auth->appHash($code2 . $category->slug . $user->pseudo . $code1);
                $formCode[$category->id] = $token;
            }

            $this->render('admin/categories/index.twig', compact('categories', 'paginationOptions', 'formCode', 'vars'));
        } else {
            $this->renderErrorNotAdmin();
        }
    }

    /**
     * @param array $vars
     * @throws \Core\ORM\Classes\ORMException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function update(array $vars): void
    {
        $u_success = false;
        $u_error = false;

        if (!empty($_POST) &&
            isset($_POST['name']) &&
            isset($_POST['slug'])
        ) {
            $resultUpdate = $this->categoryModel->updateCategory($_POST, $vars['id']);

            if ($resultUpdate) {
                $u_success = true;
            } else {
                $u_error = true;
            }
        }

        $category = $this->select->select([
            'categories' => ['id', 'name', 'slug']
        ])->from('categories')
            ->singleItem()
            ->where(['id' => $vars['id']])
            ->execute($this->categoryModel);

        $form = new BootstrapForm(' offset-sm-2 col-sm-8 loginForm');

        if ($u_success) {
            $form->item("<h4 class='success'>Modification réalisée avec succés !</h4>");
        } elseif ($u_error) {
            $form->item("<h4 class='error'>Une erreur est survenue !</h4>");
        }

        $form->input('name', 'Nom de la catégorie', $category->name);
        $form->input('slug', 'Slug de la catégorie', $category->slug);
        $form = $form->submit('Valider');

        $this->render('admin/categories/update.twig', compact('category', 'form'));
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
        if ($this->auth->logged($_SESSION['user'])) {

            $u_error = false;
            $u_success = false;
            $errorMessage = '';

            if (!empty($_POST) &&
                isset($_POST['name']) &&
                isset($_POST['slug'])
            ) {
                try {
                    $category = $this->addCategory();
                } catch (ORMException $e) {
                    $u_error = true;
                    $errorMessage = $e->getMessage();
                }

                if (!$u_error) {
                    $u_success = true;
                }
            }

            $form = new BootstrapForm(' offset-sm-2 col-sm-8 loginForm');

            if ($u_success) {
                $form->item("<h4 class='success'>Ajout réalisé avec succés !</h4>");
            } elseif ($u_error) {
                $form->item("<h4 class='error'>{$errorMessage}</h4>");
            }

            $form->input('name', 'Nom de la catégorie', $category->name);
            $form->input('slug', 'Slug de la catégorie', $category->slug);
            $form = $form->submit('Valider');

            $this->render('/admin/categories/add.twig', compact('form'));
        } else {
            $this->renderNotLog();
        }
    }

    /**
     * Vérifie que l'utilisateur est connecté et administrateur.
     * Vérifie que toutes les variables de POST sont présentes.
     * Récupère le "slug" de la catégorie dont l'ID est passé en POST.
     * Crée le hash correspondant au pseudo de l'utilisateur et au slug de la catégorie.
     * Vérifie que le hash correspondent à celui envoyé en POST.
     * Si tout OK supprime la catégorie.
     *
     * @throws ORMException
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function delete(): void
    {
        $userConnected = $_SESSION['user'];

        if ($this->auth->isAdmin($userConnected)) {
            if(!empty($_POST) && isset($_POST['token']) && isset($_POST['id'])) {

                $category = $this->select->select(['categories' => ['id', 'slug']])
                    ->from('categories')
                    ->where(['id' => $_POST['id']])
                    ->singleItem()
                    ->execute($this->categoryModel);

                $code1 = strlen($userConnected->pseudo);
                $code2 = strlen($category->slug);
                $token = $this->auth->appHash($code2 . $category->slug . $userConnected->pseudo . $code1);

                if ($token === $_POST['token']) {
                    (new ORMController())->delete($category, $this->categoryModel);

                    /*Renvoie vers la page d'administration des posts soit
                    --> à la page en cours s'il est envoyé en POST
                    --> sinon vers la 1ère page*/
                    $vars = [];
                    $vars['id'] = (isset($_POST['indexId'])) ? $_POST['id'] : '1';
                    $this->index($vars);
                } else {
                    $this->render('admin/categories/index.twig', []);
                    throw new \Exception("Une erreur est survenue lors de la suppression de la catégorie, veuillez réessayer.");
                }
            } else {
                $this->render('admin/categories/index.twig', []);
                throw new \Exception("Une erreur est survenue lors de la suppression de la catégorie, veuillez réessayer.");
            }
        }
    }

    /**
     * @param string $id
     * @throws ORMException
     */
    private function updateCategory(string $id)
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
     * @return Category
     * @throws ORMException
     */
    private function addCategory(): Category
    {
        if (empty($_POST['name'])) {
            throw new ORMException('Le champ "Nom de la catégorie" doit être renseigné !');
        }
        if (empty($_POST['slug'])) {
            throw new ORMException('Le champ "Slug de la catégorie" doit être renseigné !');
        }

        $ormTable = new ORMTable('categories');
        $ormTable->constructWithStdclass($this->categoryModel->ORMShowColumns());

        $category = new Category($ormTable, true);
        $category->name = $_POST['name'];
        $category->slug = $_POST['slug'];
        $category->setPrimaryKey(['id']);

        (new ORMController())->save($category, $this->categoryModel);

        return $category;
    }
}
