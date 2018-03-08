<?php

namespace Core\ORM;

use Core\Model\Model;
use DateTime;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
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
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ORMException
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

        $model = $this->container->get($tableName);

        $model->createTable($tableName, $statement);
    }

    /**
     * Récupère l'entité de type ORMEntity qu'il va devoir sauvegarder
     * Lance la fonction columnsDefinition() pour créer les colonnes qui se trouve dans l'ORMEntity et récupérer les valeurs
     * --> cette dernière vérifie également que les valeurs correspondent à la définition des colonnes
     * Vérifie ensuite que les colonnes existe dans la Table
     * Si tout est OK enregistre l'objet dans la table
     *
     * @param ORMEntity $entity
     * @throws ORMException
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function save(ORMEntity $entity): void
    {
        $columns = $values = '';

        try {
            /** @var Model $model */
            $model = $this->container->get($entity->getTableName());
        } catch (DependencyException | NotFoundException $e) {
            throw new ORMException("Le model demandé n'existe pas");
        }

        $columnsResults = $model->showColumns();

        $this->verifAllColumnsIsDefined($entity->getColumns(), $columnsResults, $entity);

        $this->columnsDefinition($entity->getColumns(), $entity, $columnsResults, $columns, $values);

        $model->insert("INSERT INTO {$entity->getTableName()} ({$columns}) VALUES ({$values})");
    }

    /**
     * Récupère les colonnes de l'ORMEntity
     * Vérifie que la colonne appelée n'est pas id et si n'est pas le cas ajoute le nom de la colonne dans $columnArray
     * Appelle valuesDefinition() pour récupérer les informations dans l'ORMEntity
     * Place la string partir de $columnArray dans $columnString
     *
     * @param array $columns
     * @param ORMEntity $entity
     * @param array $columnsTable
     * @param string $columnsString
     * @param string $valuesString
     * @return void
     * @throws ORMException
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    private function columnsDefinition(array $columns, ORMEntity $entity, array $columnsTable, string &$columnsString, string &$valuesString): void
    {
        $columnsEntity = [];

        foreach ($columns as $column) {
            if ($column['columnName'] !== 'id') {
                $columnsEntity[] .= $column['columnName'];
            }
        }

        $this->valuesDefinition($columnsEntity, $entity, $columnsTable, $valuesString);

        $columnsString = implode(', ', $columnsEntity);
    }

    /**
     * Récupère les colones créées lors de la fonction columnsDefinition()
     * Récupère la valeur dans l'objet stocké dans l'objet
     *
     * @param array $columnsEntity
     * @param ORMEntity $entity
     * @param array $columnsTable
     * @param string $valuesString
     * @return void
     * @throws ORMException
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    private function valuesDefinition(array $columnsEntity, ORMEntity $entity, array $columnsTable, string &$valuesString): void
    {
        $values = [];

        foreach ($columnsEntity as $columnEntity) {
            $typeArray = [];

            foreach ($columnsTable as $columnTable) {
                if (strtolower($columnTable->Field) === strtolower($columnEntity)) {
                    $typeColumn = str_replace(')', '', $columnTable->Type);

                    $typeArray = explode('(', $typeColumn);

                    break;
                }
            }

            //Vérifie si $typeArray est vide, si c'est le cas ça veut dire que la colonne n'est pas définie dans la base de données
            if (!empty($typeArray)) {
                //Vérifie la valeur, si OK la renvoie en forçant le type (par sécurité) et en échappant les caractères HTML (sécurité pour les string)
                $result = $this->verifValue($typeArray, $entity->$columnEntity, $columnEntity);

                if (is_null($result)) {
                    throw new ORMException("La valeur de la colonne \"{$columnEntity}\" ne correspond pas au type inscrit dans la base de données");
                } else {
                    $values[] = $result;
                }
            } else {
                throw new ORMException("La colonne \"{$columnEntity}\" n'est pas définie dans la base de données");
            }
        }

        $valuesString = implode(', ', $values);
    }

    /**
     * Récupère toutes les colonnes de l'ORMEntity, vérifie si elles ont une valeur et les ajoute à un tableau
     * Récupère toutes les colonnes de la table et les ajoute à un tableau
     * Compare les deux tableaux et si des éléments de la Table ne se trouvent pas dans l'ORMEntity renvoie une erreur
     *
     * @param array $columnsEntity Colonnes récupérées dans l'ORMEntity à sauvegarder
     * @param array $columnsResults Colonnes récupérées dans la Table correspondant à l'ORMEntity
     * @param ORMEntity $entity
     * @return void
     * @throws ORMException
     */
    private function verifAllColumnsIsDefined(array $columnsEntity, array $columnsResults, ORMEntity $entity): void
    {
        foreach ($columnsEntity as $columnEntity) {
            $att = $columnEntity['columnName'];

            if ($att === 'id' || !empty($entity->$att)) {
                $columnsEntityName[] = $columnEntity['columnName'];
            }
        }

        foreach ($columnsResults as $columnResults) {
            if ($columnResults->Null === 'NO') {
                $columnsResultsNotNull[] = $columnResults->Field;
            }
        }

        if (!empty($result = array_diff($columnsResultsNotNull, $columnsEntityName))) {
            $columns = implode(', ', $result);

            throw new ORMException("La/Les colonne(s) \"{$columns}\" ne doivent pas être vides");
        }
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
            throw new ORMException('Le type SQL demandé n\'est pas valide');
        }
    }

    /**
     * Récupère le tableau d'option :
     * - Vérifie qu'il n'est pas vide
     * - S'il n'est pas vide, le parcours avec clé et valeur
     * - Si la valeur (qui est un booléen) est à true continue
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

        if ($result > 1) {
            throw new ORMException('La table ne doit pas contenir plus d\'une clé primaire !');
        } elseif ($result <= 0 || $result === false) {
            throw new ORMException('La table doit contenir au moins une clé primaire !');
        }
    }

    /**
     * Vérifie si la valeur à placer est du bon type par rapport au type demandé par la base de données
     *
     * @param array $valueTypeAndSize De la base de données
     * @param mixed $value Valeur à vérifier
     * @param string $columnName
     * @return mixed
     * @throws ORMException
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    private function verifValue(array $valueTypeAndSize, $value, string $columnName)
    {
        if (in_array($valueTypeAndSize[0], $this->container->get('SQL.string'))) {
            //Vérifie si une longueur maximale de texte est donnée, si oui vérifie si on est inférieur
            if (isset($valueTypeAndSize[1])) {
                if (strlen($value) <= $valueTypeAndSize[1]) {
                    return (is_string($value)) ? '"' . htmlspecialchars($value) . '"' : null;
                } else {
                    throw new ORMException("La longueur du texte contenu dans \"{$columnName}\" est trop importante");
                }
            } else {
                return (is_string($value)) ? '"' . htmlspecialchars($value) . '"' : null;
            }
        } elseif (in_array($valueTypeAndSize[0], $this->container->get('SQL.numeric'))) {
            return (is_numeric($value)) ? (int)$value : null;
        } elseif (in_array($valueTypeAndSize[0], $this->container->get('SQL.date'))) {
            return ($value instanceof DateTime) ? new DateTime($value) : null;
        }

        throw new ORMException("Le format de \"{$columnName}\" n'existe pas");
    }
}
