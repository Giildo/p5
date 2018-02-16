<?php

namespace Core\Model;

use Core\Database\Database;
use PDO;
use PDOStatement;

class Model
{
    /**
     * @var \PDO
     */
    protected $pdo;

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
     * @return PDOStatement
     */
    public function setFetchMode(PDOStatement $result, $class): PDOStatement
    {
        $result->setFetchMode(PDO::FETCH_CLASS, $class);
        return $result;
    }
}
