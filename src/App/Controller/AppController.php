<?php

namespace App\Controller;

use Core\Controller\Controller;
use Core\Controller\ControllerInterface;

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
     * @param EntityInterface $entity
     * @return string[]
     */
    protected function createPostWithEntity(array $keys, array $posts, EntityInterface $entity): array
    {
        foreach ($keys as $key) {
            $method = 'get' . ucfirst($key);
            $posts[$key] = (empty($posts[$key])) ? $entity->$method() : $posts[$key];
        }

        return $posts;
    }

    /**
     * @param EntityInterface[] $entities
     * @param string $method
     * @return string[]
     */
    protected function createSelectOptions(array $entities, string $method): array
    {
        $selectOptions = [];
        foreach ($entities as $entity) {
            $selectOptions[] = $entity->$method();
        }
        return $selectOptions;
    }
}
