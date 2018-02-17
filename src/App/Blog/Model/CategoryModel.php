<?php

namespace App\Blog\Model;

use Core\Model\Model;

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
        $result = $this->pdo->prepare("SELECT id FROM `{$this->table}` WHERE slug = :slug");
        $result->bindParam('slug', $slug);
        $result->execute();
        return $result->fetch()->id;
    }
}
