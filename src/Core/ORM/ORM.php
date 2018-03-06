<?php

namespace Core\ORM;

use DI\Container;
use PDO;

class ORM
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var null|PDO
     */
    private $pdo;

    /**
     * @var array
     */
    private $SQLType = [];

    /**
     * ORM constructor
     *
     * @param Container $container
     * @param PDO $pdo
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function __construct(Container $container, PDO $pdo)
    {
        $this->container = $container;
        $this->pdo = $pdo;
        $this->SQLType = $this->container->get('SQL.types');
    }

    /**
     * Récupère les éléments d'un objet ORM pour créer une nouvelle table dans la BDD
     *
     * @param string $tableName
     * @param array $columns
     * @throws ORMException
     * @return void
     */
    public function createTable(string $tableName, array $columns): void
    {
        $statement = '(';
        $end = array_pop(array_keys($columns));
        $i = 0;

        foreach ($columns as $column) {
            foreach ($column as $key => $value) {
                switch ($key) {
                    case 'columnName':
                        $statement .= $value;
                        break;

                    case 'columnType':
                        $this->columnTypeDefinition($statement, $value);
                        break;

                    case 'max':
                        $statement .= (!is_null($value)) ? "({$value})" : '';
                        break;

                    case 'options':
                        $this->optionsDefinition($statement, $value);
                        break;
                }
            }

            //Vérifie si c'est la dernière instance du tableau ou non
            if ($i === $end) {
                $statement .= ')';
            } else {
                $statement .= ', ';
            }

            $i++;
        }

        $this->verifStatement($statement);

        $this->pdo->query("CREATE TABLE IF NOT EXISTS {$tableName} " . $statement . " ENGINE=INNODB");
    }

    /**
     * Vérifie si le type récupéré dans le value existe dans la liste des types de SQL définie dans la config
     * Si ce n'est pas le cas renvoie une exception
     * Sinon ajoute au statement le type en majuscule
     *
     * @param string $statement
     * @param string $value
     * @throws ORMException
     * @return void
     */
    private function columnTypeDefinition(string &$statement, string $value): void
    {
        if (in_array(strtolower($value), $this->SQLType)) {
            $statement .= ' ' . strtoupper($value);
        } else {
            throw new ORMException('Le type SQL ne fait pas partie de la liste', 1);
        }
    }

    /**
     * Récupère le tableau d'option :
     * Vérifie qu'il n'est pas vide
     * S'il n'est pas vide, le parcours avec clé et valeur
     * Si la valeur (qui est un booléen) est à true continue
     * Récupère la clé et selon celle-ci inscrira ce qu'il faut dans la ligne
     *
     * @param string $statement
     * @param array $options
     * @return void
     */
    private function optionsDefinition(string &$statement, array $options): void
    {
        $notNull = true;

        if (!empty($options)) {
            foreach ($options as $key => $value) {
                $statement .= ($key !== 'not_null') ? ' ' : '';
                switch ($key) {
                    case 'auto_increment':
                        $statement .= 'AUTO_INCREMENT';
                        break;

                    case 'primary':
                        $statement .= 'PRIMARY KEY';
                        break;

                    case 'not_null':
                        $notNull = false;
                        break;
                }
            }
        }

        $statement .= ($notNull) ? ' NOT NULL' : '';
    }

    /**
     * Récupère le statement et vérifie :
     * Qu'il n'y a pas plus d'une clé primaire
     * Qu'il y a au moins une clé primaire
     * --> Sinon renvoie des ORMException
     *
     * @param string $statement
     * @throws ORMException
     * @return void
     */
    private function verifStatement(string $statement): void
    {
        $result = preg_match_all('#PRIMARY KEY#', $statement);

        if($result > 1) {
            throw new ORMException('La table ne doit pas contenir plus d\'une clé primaire !', 2);
        } elseif ($result <= 0 || $result === false) {
            throw new ORMException('La table doit contenir au moins une clé primaire !', 3);
        }
    }
}
