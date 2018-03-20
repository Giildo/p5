<?php

namespace App\Controller;

use App\Admin\Model\AdminModel;
use App\Admin\Model\UserModel;
use App\Entity\User;
use Core\Controller\Controller;
use Core\Controller\ControllerInterface;
use Core\ORM\Classes\ORMEntity;
use Core\ORM\Classes\ORMException;

class AppController extends Controller implements ControllerInterface
{
    /**
     * Récupère l'utilisateur connecté dans la BDD
     *
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function findUserConnected(): ?User
    {
        $userModel = $this->container->get(UserModel::class);
        $adminModel = $this->container->get(AdminModel::class);

        if (!empty($_SESSION['user'])) {
            try {
                $user = $this->select->select([
                    'users' => ['pseudo', 'admin'],
                    'admin' => ['id', 'name']
                ])
                    ->from('users')
                    ->innerJoin('admin', ['admin.id' => 'users.admin'])
                    ->singleItem()
                    ->where(['users.id' => $_SESSION['user']->id])
                    ->insertEntity(['admin' => 'users'], ['id' => 'admin'], 'oneToOne')
                    ->execute($userModel, $adminModel);
            } catch (ORMException $e) {
                $this->auth->logout();
                return null;
            }
        } else {
            $this->auth->logout();
            return null;
        }

        return $user;
    }

    /**
     * Génère un tableau qui va regrouper les éléments passés en post ou non
     *
     * @param array $keys
     * @return string[]
     */
    protected function createPost(array $keys): array
    {
        $post = [];

        foreach ($keys as $key) {
            $post[$key] = (isset($_POST[$key])) ? $_POST[$key] : '';
        }

        return $post;
    }

    /**
     * Génère un tableau qui va regrouper les éléments passés en post ou non
     *
     * @param array $keys
     * @param array $posts
     * @param ORMEntity $entity
     * @return string[]
     */
    protected function createPostWithEntity(array $keys, array $posts, ORMEntity $entity): array
    {
        foreach ($keys as $key) {
            $method = 'get' . ucfirst($key);
            $posts[$key] = (empty($posts[$key])) ? $entity->$method() : $posts[$key];
        }

        return $posts;
    }

    /**
     * @param ORMEntity[] $entities
     * @param string $att
     * @return string[]
     */
    protected function createSelectOptions(array $entities, string $att): array
    {
        $selectOptions = [];
        foreach ($entities as $entity) {
            $selectOptions[] = $entity->$att;
        }
        return $selectOptions;
    }

    /**
     * Récupère toutes les catégories pour la gestion des catégories sur les pages du blog
     *
     * @return \Core\ORM\Classes\ORMEntity|\Core\ORM\Classes\ORMEntity[]
     * @throws \Core\ORM\Classes\ORMException
     * @return ORMEntity[]
     */
    protected function findCategories(): array
    {
        return $this->select->select(['categories' => ['id', 'name', 'slug']])
            ->from('categories')
            ->execute($this->categoryModel);
    }

    /**
     * @param string $nameView
     * @param array|null $twigVariable
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function render(string $nameView, ?array $twigVariable = []): void
    {
        $user = $this->findUserConnected();
        $twigVariable['sessionConfirmConnect'] = (!is_null($user)) ? $this->auth->logged($user) : false;
        $twigVariable['sessionAdmin'] = $_SESSION['user']->admin->id === 1;

        parent::render($nameView, $twigVariable);
    }

    /**
     * @param array $vars
     * @param int $nbItem
     * @param null|string $optionLimit
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function pagination(array $vars, int $nbItem, ?string $optionLimit = 'blog.limit.post'): array
    {
        $pagination = [];

        $pagination['limit'] = $this->container->get($optionLimit);

        $pagination['id'] = (int)$vars['id'];

        $pagination['pageNb'] = (int)ceil($nbItem / $pagination['limit']);
        $pagination['start'] = ($pagination['limit'] * ($pagination['id'] - 1));

        $pagination['next'] = ($pagination['id'] + 1 <= $pagination['pageNb']) ? $pagination['id'] + 1 : null;
        $pagination['previous'] = ($pagination['id'] - 1 >= 1) ? $pagination['id'] - 1 : null;

        return $pagination;
    }

    /**
     * @param array $paginationOptions
     * @param string $path
     */
    protected function paginationMax(array $paginationOptions, string $path): void
    {
        if ($paginationOptions['pageNb'] < $paginationOptions['id']) {
            $this->redirection($path . $paginationOptions['pageNb']);
        }
    }
}
