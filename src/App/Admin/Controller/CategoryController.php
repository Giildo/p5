<?php

namespace App\Admin\Controller;

use App\Blog\Model\CategoryModel;
use App\Entity\Category;
use Core\Controller\Controller;
use Core\Controller\ControllerInterface;
use Core\Form\BootstrapForm;

class CategoryController extends Controller implements ControllerInterface
{
    /**
     * @var CategoryModel
     */
    protected $categoryModel;

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
        if ($this->auth->isAdmin()) {
            $nbPage = $this->categoryModel->count();

            $paginationOptions = $this->pagination($vars, $nbPage, 'admin.limit.category');

            $categories = $this->categoryModel->findAll(Category::class);

            $this->render('admin/categories/index.twig', compact('categories', 'paginationOptions'));
        } else {
            $this->renderErrorNotAdmin();
        }
    }

    /**
     * @param array $vars
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

        $category = $this->categoryModel->find($vars['id'], Category::class);

        $keys = ['name', 'slug'];
        $posts = $this->createPost($keys);
        $posts = $this->createPostWithEntity($keys, $posts, $category);

        $form = new BootstrapForm(' offset-sm-2 col-sm-8 loginForm');

        if ($u_success) {
            $form->item("<h4 class='success'>Modification réalisée avec succés !</h4>");
        } elseif ($u_error) {
            $form->item("<h4 class='error'>Une erreur est survenue !</h4>");
        }

        $form->input('name', 'Nom de la catégorie', $posts['name']);
        $form->input('slug', 'Slug de la catégorie', $posts['slug']);
        $form = $form->submit('Valider');

        $this->render('admin/categories/ORMUpdate.twig', compact('category', 'form'));
    }
}
