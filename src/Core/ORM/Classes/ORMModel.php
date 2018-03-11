<?php

namespace Core\ORM\Classes;

use Core\ORM\Interfaces\ORMModelInterface;
use PDO;

class ORMModel implements ORMModelInterface
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * ORMModel constructor.
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère un statement pour ajouter un élément dans la base de données
     *
     * @param string $statement
     * @return void
     */
    public function ORMInsert(string $statement): void
    {
        $this->pdo->query($statement);
    }

    /**
     * Récupère un statement pour modifier un élément dans la base de données
     *
     * @param string $statement
     * @param string $primaryKey
     * @param $primaryKeyValue
     * @return void
     */
    public function ORMUpdate(string $statement, string $primaryKey, $primaryKeyValue): void
    {
        $results = $this->pdo->prepare($statement);
        $results->bindParam($primaryKey, $primaryKeyValue);
        $results->execute();
    }

    /**
     * Récupère les colonnes dans la base de données et les retourne
     *
     * @return array
     */
    public function ORMShowColumns(): array
    {
        $results = $this->pdo->query("SHOW COLUMNS FROM {$this->table}");
        return $results->fetchAll();
    }

    /**
     * Crée une table dans la base de données
     *
     * @param string $statement
     * @return void
     */
    public function ORMCreateTable(string $statement): void
    {
        $this->pdo->query("CREATE TABLE IF NOT EXISTS {$this->table} " . $statement . " ENGINE=INNODB");
    }

    public function ORMFind(string $statement, string $entityType): array
    {
        $result = $this->pdo->query($statement);
        $result->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $entityType);
        return $result->fetchAll();
    }
}
