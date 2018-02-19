<?php

namespace App\Admin\Controller;

use App\Blog\Model\CategoryModel;
use App\Entity\Category;
use Core\Controller\Controller;
use Core\Controller\ControllerInterface;

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
}
