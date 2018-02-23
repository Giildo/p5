<?php

namespace Core\Model;

use App\Entity\Post;
use Core\Database\Database;
use Core\Entity\Entity;
use Core\Entity\EntityInterface;
use PDO;
use PDOStatement;

/**
 * Class Model
 * @package Core\Model
 */
class Model
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var null
     */
    protected $table = null;

    /**
     * Model constructor.
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->pdo = $database->getPDO();
    }

    /**
     * @param PDOStatement $result
     * @param $class
     * @return void
     */
    public function setFetchMode(PDOStatement $result, $class): void
    {
        $result->setFetchMode(PDO::FETCH_CLASS, $class);
    }

    /**
     * @param string $entity
     * @param int|null $start
     * @param int|null $limit
     * @param bool|null $order
     * @param null|string $orderBy
     * @return array
     */
    public function findAll(
        string $entity,
        ?int $start = null,
        ?int $limit = null,
        ?bool $order = false,
        ?string $orderBy = null
    ): array {
        $orderBy = ($order) ? $orderBy : '';

        $limitStart = '';
        if ($start !== null && $limit !== null) {
            $limitStart = 'LIMIT :limit OFFSET :start';
        }

        $result = $this->pdo->prepare("SELECT * FROM `{$this->table}`{$orderBy} {$limitStart}");

        if ($start !== null && $limit !== null) {
            $result->bindParam(':start', $start, PDO::PARAM_INT);
            $result->bindParam(':limit', $limit, PDO::PARAM_INT);
        }

        $result->setFetchMode(PDO::FETCH_CLASS, $entity);
        $result->execute();

        return $result->fetchAll();
    }

    /**
     * Récupère un post en fonction de la catégorie
     *
     * @param string $statement
     * @param string $columnName
     * @param int $columnId
     * @param string $entity
     * @param int|null $start
     * @param int|null $limit
     * @param bool|null $order
     * @param null|string $orderBy
     * @return array
     */
    protected function findAllByColumn(
        string $statement,
        string $columnName,
        int $columnId,
        string $entity,
        ?int $start,
        ?int $limit,
        ?bool $order = false,
        ?string $orderBy = null
    ): array {
        $orderBy = ($order) ? $orderBy : '';

        $startLimit = '';
        if ($start !== null && $limit !== null) {
            $startLimit = 'LIMIT :limit OFFSET :start';
        }

        $result = $this->pdo->prepare($statement . $orderBy . $startLimit);

        if ($start !== null && $limit !== null) {
            $result->bindParam(':start', $start, PDO::PARAM_INT);
            $result->bindParam(':limit', $limit, PDO::PARAM_INT);
        }

        $result->bindParam($columnName, $columnId);
        $result->setFetchMode(PDO::FETCH_CLASS, $entity);
        $result->execute();

        return $result->fetchAll();
    }

    /**
     * @param string $id
     * @param string $entity
     * @return Post|bool
     */
    public function find(string $id, string $entity)
    {
        $result = $this->pdo->prepare("SELECT * FROM `{$this->table}`WHERE id=:id");
        $result->bindParam(':id', $id);
        $result->setFetchMode(PDO::FETCH_CLASS, $entity);
        $result->execute();

        return $result->fetch();
    }

    /**
     * Récupère l'ID d'un élément avec la valeur d'une colonne
     *
     * @param string $columnName
     * @param string $value
     * @param string $entity
     * @return EntityInterface
     */
    public function findIdByColumn(string $columnName, string $value, string $entity): EntityInterface
    {
        $result = $this->pdo->prepare("SELECT id FROM {$this->table} WHERE {$columnName} = :value");
        $result->bindParam('value', $value);
        $result->setFetchMode(PDO::FETCH_CLASS, $entity);
        $result->execute();

        return $result->fetch();
    }

    /**
     * Compte le nombre d'item dans la base de données
     *
     * @return int
     */
    public function count(): int
    {
        return $this->pdo->query("SELECT COUNT(id) FROM {$this->table}")->fetchColumn();
    }
}
