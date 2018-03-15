<?php

namespace App\Controller;

use Core\Controller\Controller;
use Core\Controller\ControllerInterface;
use Core\ORM\Classes\ORMEntity;

class AppController extends Controller implements ControllerInterface
{
    use Pagination;

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
}
