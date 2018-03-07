<?php

namespace Core\ORM;

use DateTime;
use Psr\Container\ContainerInterface;
use stdClass;

class ORMSelect
{
    /**
     * @var string
     */
    private $statement;

    private $tableName;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var ORMEntity
     */
    private $ORMEntity;

    public function __construct(ContainerInterface $container, ORMEntity $ORMEntity)
    {
        $this->statement = 'SELECT';
        $this->container = $container;
        $this->ORMEntity = $ORMEntity;
    }

    public function select(?string $columnsName = '*'): ORMSelect
    {
        $this->finalSpace();

        $this->statement .= $columnsName;

        return $this;
    }

    public function from(string $tableName): ORMSelect
    {
        $this->finalSpace();
        $this->tableName = $tableName;

        if (substr($this->statement, -7) === 'SELECT ') {
            $this->statement .= '* ';
        }

        $this->statement .= 'FROM ' . $tableName;

        return $this;
    }

    /**
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function execute(): array
    {
        $model = $this->container->get($this->container->get($this->tableName));

        $results = $model->findORM($this->statement);
        $columnsTable = $model->showColumns();

        $entityArray = [];

        $this->ORMEntity->setTableName($this->tableName);
        /** @var stdClass $columnTable */
        foreach ($columnsTable as $columnTable) {
            $this->ORMEntity->constructWithStdclass($columnTable);
        }

        foreach ($results as $result) {
            $entity = new ORMEntity();
            foreach ($this->ORMEntity->getColumns() as $column) {
                $att = $column['columnName'];
                $entity->$att = $this->resultValue($column, $result);
            }
            $entity->setColumns($this->ORMEntity->getColumns());
            $entityArray[] = $entity;
        }

        return $entityArray;
    }

    private function finalSpace(): void
    {
        if (substr($this->statement, -1) !== ' ') {
            $this->statement .= ' ';
        }
    }

    /**
     * @param array $column
     * @param stdClass $result
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function resultValue(array $column, stdClass $result)
    {
        $att = $column['columnName'];
        if (isset($result->$att)) {
            if (in_array($column['columnType'], $this->container->get('SQL.string'))) {
                return (string)$result->$att;
            } elseif (in_array($column['columnType'], $this->container->get('SQL.numeric'))) {
                return (int)$result->$att;
            } elseif (in_array($column['columnType'], $this->container->get('SQL.date'))) {
                return new DateTime($result->$att);
            }
        } else {
            return null;
        }
    }
}
