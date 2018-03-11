<?php

namespace Core\ORM\Classes;

use Core\ORM\Interfaces\ORMModelInterface;
use DateTime;
use stdClass;

class ORMSelect
{
    /**
     * @var string
     */
    private $statement;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $configFiles;

    public function __construct(string $configFiles)
    {
        $this->statement = 'SELECT';
        $this->typesSQLDefinition();
        $this->configFiles = $configFiles;
    }

    use ORMConfigSQL;

    public function select(?string $columnsName = '*'): ORMSelect
    {
        $this->finalSpace();

        $this->statement .= $columnsName;

        return $this;
    }

    public function from(string $tableName): ORMSelect
    {
        if ($this->statement === 'SELECT') {
            $this->select();
        }

        $this->finalSpace();

        $this->tableName = $tableName;

        $this->statement .= 'FROM ' . $tableName;

        return $this;
    }

    /**
     * @param ORMModelInterface $model
     * @return array
     */
    public function execute(ORMModelInterface $model): array
    {
        $entityList = require $this->configFiles;
        $entityType = '';

        foreach ($entityList as $entityName => $entityPath) {
            if ($this->tableName === $entityName) {
                $ormTable = new ORMTable($this->tableName);
                $ormTable->constructWithStdclass($model->ORMShowColumns());
                $entityType = $entityPath;
                $entity = new $entityPath($ormTable);
            }
        }

        return $model->ORMFind($this->statement, $entityType);
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
     */
    private function resultValue(array $column, stdClass $result)
    {
        $att = $column['columnName'];
        if (isset($result->$att)) {
            if (in_array($column['columnType'], $this->sqlString)) {
                return (string)htmlspecialchars($result->$att);
            } elseif (in_array($column['columnType'], $this->sqlNumeric)) {
                return (int)$result->$att;
            } elseif (in_array($column['columnType'], $this->sqlDate)) {
                return new DateTime($result->$att);
            }
        } else {
            return null;
        }
    }
}
