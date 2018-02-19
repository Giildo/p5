<?php

namespace App\Blog\Model;

use Core\Model\Model;
use PDO;

/**
 * Fait le lien pour les Posts
 *
 * Class CategoryModel
 * @package App\Blog\Model
 */
class CategoryModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'categories';

    /**
     * Retourne l'ID de la category en fonction de son slug
     *
     * @param string $slug
     * @return int
     */
    public function findBySlug(string $slug): int
    {
        $result = $this->pdo->prepare("SELECT id FROM categories WHERE slug = :slug");
        $result->bindParam('slug', $slug);
        $result->execute();
        return $result->fetch()->id;
    }

    public function updateCategory(array $posts, int $id): bool
    {
        foreach ($posts as $post) {
            if (empty($post)) {
                return false;
            }
        }

        $result = $this->pdo->prepare("UPDATE categories SET name = :name, slug = :slug WHERE id = :id");
        $result->bindParam('name', $posts['name']);
        $result->bindParam('slug', $posts['slug']);
        $result->bindParam('id', $id, PDO::PARAM_INT);
        return $result->execute();
    }
}
