<?php

namespace App\Admin\Controller;

use App\Blog\Model\CategoryModel;
use Core\Controller\Controller;
use Core\Controller\ControllerInterface;

class CategoryController extends Controller implements ControllerInterface
{
    /**
     * @var CategoryModel
     */
    protected $categoryModel;

    /**
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index(): void
    {
        if (isset($_SESSION['user'])) {
            if ($_SESSION['user']['idAdmin'] === '1') {
                $categories = $this->categoryModel->findAll();

                $this->render('admin/categories/index.twig', compact('categories'));
            } else {
                $this->renderErrorNotAdmin();
            }
        } else {
            $this->renderNotLog();
        }
    }
}
