<?php

namespace Core\ORM\Classes;

use Core\Model\Model;
use Core\ORM\Interfaces\ORMModelInterface;
use stdClass;

/**
 * Class ORMSelect
 * @package Core\ORM\Classes
 */
class ORMSelect
{
    /**
     * @var array
     */
    private $statement = [];

    /**
     * @var string
     */
    private $configFiles;

    /**
     * @var string
     */
    private $tableName;

    /**
     * ORMSelect constructor.
     * @param string $configFiles
     */
    public function __construct(string $configFiles)
    {
        $this->configFiles = $configFiles;
        $this->typesSQLDefinition();
        $this->statement['join'] = false;
    }

    use ORMConfigSQL;

    /**
     * @param null|string $columnsName
     * @return ORMSelect
     */
    public function select(?string $columnsName = '*'): ORMSelect
    {
        $this->statement['select'] = $columnsName;

        return $this;
    }

    /**
     * @param string $tableName
     * @return ORMSelect
     */
    public function from(string $tableName): ORMSelect
    {
        $this->tableName = $tableName;
        $this->statement['from'] = $tableName;

        return $this;
    }

    /**
     * @param array $whereOptions
     * @return ORMSelect
     */
    public function where(array $whereOptions): ORMSelect
    {
        $this->statement['where'] = $whereOptions;

        return $this;
    }

    /**
     * @param array $orderByOptions
     * @return ORMSelect
     */
    public function orderBy(array $orderByOptions): ORMSelect
    {
        $this->statement['orderBy'] = $orderByOptions;

        return $this;
    }

    /**
     * @param string $limit
     * @param null|string $offset
     * @return $this
     */
    public function limit(string $limit, ?string $offset = '0'): ORMSelect
    {
        $this->statement['limit'] = $limit;
        $this->statement['offset'] = $offset;

        return $this;
    }

    /**
     * @param string $tableJoined
     * @param array $joinOptions
     * @return ORMSelect
     */
    public function innerJoin(string $tableJoined, array $joinOptions): ORMSelect
    {
        $this->statement['joinType'] = 'INNER JOIN';
        $this->join($tableJoined, $joinOptions);

        return $this;
    }

    /**
     * @param string $tableJoined
     * @param array $joinOptions
     * @return ORMSelect
     */
    public function rightJoin(string $tableJoined, array $joinOptions): ORMSelect
    {
        $this->statement['joinType'] = 'RIGHT JOIN';
        $this->join($tableJoined, $joinOptions);

        return $this;
    }

    /**
     * @param string $tableJoined
     * @param array $joinOptions
     * @return ORMSelect
     */
    public function leftJoin(string $tableJoined, array $joinOptions): ORMSelect
    {
        $this->statement['joinType'] = 'LEFT JOIN';
        $this->join($tableJoined, $joinOptions);

        return $this;
    }

    /**
     * @param array $options
     * @return ORMSelect
     */
    public function innerOptions(array $options): ORMSelect
    {
        $this->statement['innerOptions'] = $options;

        return $this;
    }

    /**
     * @param ORMModelInterface[] $models
     * @return mixed
     * @throws ORMException
     */
    public function execute(ORMModelInterface  ...$models)
    {
        //Crée le $statement et le contruit avec les différentes parties du tableau
        $statement = '';
        $this->statementDefinition($statement);

        //Récupère le fichier de config pour récupérer la construction des entités
        $entityList = require $this->configFiles;

        //Crée les différents ORMTables
        $ormTables = [];
        /** @var Model $model */
        foreach ($models as $model) {
            $ormTable = new ORMTable($model->getTable());
            $ormTable->constructWithStdclass($model->ORMShowColumns());
            $ormTables[$model->getTable()] = $ormTable;
        }

        //Crée les différentes entités
        $entities = [];
        foreach ($entityList as $entityName => $entityPath) {
            if ($this->tableName === $entityName) {
                $entities[$entityName] = new $entityPath();
            }
            if ($this->statement['join']) {
                if ($this->statement['tableJoined'] === $entityName) {
                    $entities[$entityName] = new $entityPath();
                }
            }
        }

        //Renvoie une erreur si le tableau est vide
        if (empty($entities)) {
            throw new ORMException("L'entité n'a pas été trouvée dans le fichier de configuration \"{$this->configFiles}\".");
        }

        //Récupère les stdClass en fonction du statement
        if (isset($this->statement['where'])) {
            $items = $model->ORMFind($statement, null, $this->statement['where']);
        } else {
            $items = $model->ORMFind($statement);
        }

        //Sépare les données en fonction des entités à créer
        $stdClasses = [];
        foreach ($entities as $key => $entity) {
            $stdClasses[$key] = new stdClass();
        }

        $allStdClasses = [];
        foreach ($items as $item) {
            foreach ($entities as $key => $entity) {
                $stdClasses[$key] = new stdClass();
            }

            foreach ($item as $columnName => $value) {
                $results = explode('_', $columnName);

                $stdClass = $stdClasses[$results[0]];

                $att = $results[1];
                $stdClass->$att = $value;

                $stdClasses[$results[0]] = $stdClass;
            }
            $allStdClasses[] = $stdClasses;
        }

        //Construit les entités avec les données récupérées dans le statement
        $allEntities = [];
        foreach ($allStdClasses as $stdClasses) {
            foreach ($stdClasses as $entityName => $stdClass) {
                $entity = $entities[$entityName];
                /** @var ORMEntity $entityItem */
                $entityItem = new $entity($ormTables[$entityName]);
                $entityItem->constructWithStdclass(
                    $stdClass,
                    ['sqlString' => $this->sqlString, 'sqlDate' => $this->sqlDate, 'sqlNumeric' => $this->sqlNumeric]
                );

                if (!in_array($entityItem, $allEntities)) {
                    $allEntities[] = $entityItem;
                }
            }
        }

        //Insère les éléments les un dans les autres
        $entitiesChild = [];
        if (isset($this->statement['innerOptions'])) {
            foreach ($this->statement['innerOptions'] as $entityChild => $entityParent) {
                foreach ($allEntities as $entity) {
                    if ($entity->getTableName() === $entityChild) {
                        $entitiesChild[] = $entity;
                    } elseif ($entity->getTableName() === $entityParent) {
                        $entitiesParent = $entity;
                    }
                }
            }

            $att = $entitiesChild[0]->getTableName();
            $entitiesParent->$att = $entitiesChild;
            return $entitiesParent;
        }

        return $allEntities;
    }

    /**
     * @param string $statement
     * @throws ORMException
     */
    private function statementDefinition(string &$statement): void
    {
        $statement = 'SELECT ';
        $statement .= (isset($this->statement['select'])) ? $this->statement['select'] . ' ' : '* ';

        if (isset($this->statement['from'])) {
            $statement .= 'FROM ' . $this->statement['from'];
        } else {
            throw new ORMException("La table n'a pas été définie.");
        }

        if (isset($this->statement['joinType'])) {
            $statement .= ' ' . strtoupper($this->statement['joinType']);
            $statement .= ' ' . $this->statement['tableJoined'];
            foreach ($this->statement['joinOptions'] as $leftId => $rightId) {
                $statement .= ' ON ' . $leftId . ' = ' . $rightId;
            }
        }

        if (isset($this->statement['where'])) {
            foreach ($this->statement['where'] as $key => $value) {
                if (preg_match('#\.#', $key)) {
                    $result = explode('.', $key);
                    $key2 = ':' . $result[0] . ucfirst(strtolower($result[1]));
                } else {
                    $key2 = ':' . $key;
                }
                $statement .= ' WHERE ' . $key . "=" . $key2;
            }
        }

        if (isset($this->statement['orderBy'])) {
            foreach ($this->statement['orderBy'] as $key => $value) {
                $statement .= ' ORDER BY ' . $key . ' ' . strtoupper($value);
            }
        }

        if (isset($this->statement['limit'])) {
            $statement .= ' LIMIT ' . $this->statement['limit'];
            $statement .= (isset($this->statement['offset'])) ? ' OFFSET ' . $this->statement['offset'] : '';
        }
    }

    private function join(string $tableJoined, array $joinOptions): void
    {
        $this->statement['join'] = true;
        $this->statement['tableJoined'] = $tableJoined;
        $this->statement['joinOptions'] = $joinOptions;
    }
}
