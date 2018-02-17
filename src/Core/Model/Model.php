<?php

namespace Core\Model;

use App\Entity\Post;
use Core\Database\Database;
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
     * @param int|null $start
     * @param int|null $limit
     * @param bool|null $order
     * @param null|string $orderBy
     * @return array
     */
    public function findAll(
        ?int $start = null,
        ?int $limit = null,
        ?bool $order = false,
        ?string $orderBy = null
    ): array {
        $orderBy = ($order) ? $orderBy : '';

        if ($start === null && $limit === null) {
            $result = $this->pdo->prepare("SELECT * FROM `{$this->table}`{$orderBy}");
        } else {
            $result = $this->pdo->prepare("SELECT * FROM `{$this->table}`{$orderBy}LIMIT :start, :limit");
            $result->bindParam(':start', $start, PDO::PARAM_INT);
            $result->bindParam(':limit', $limit, PDO::PARAM_INT);
        }
        $this->setFetchMode($result, Post::class);
        $result->execute();

        return $result->fetchAll();
    }

    /**
     * @param string $id
     * @return Post|bool
     */
    public function find(string $id)
    {
        $result = $this->pdo->prepare("SELECT * FROM `{$this->table}`WHERE id=:id");
        $result->bindParam(':id', $id);
        $this->setFetchMode($result, Post::class);
        $result->execute();

        return $result->fetch();
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->pdo->query("SELECT COUNT(id) FROM {$this->table}")->fetchColumn();
    }
}
