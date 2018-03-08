<?php

namespace Core\ORM;

use DateTime;
use stdClass;

class ORMEntity
{
    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var ORMTable
     */
    protected $ORMTable;

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * ORMEntity constructor.
     * @param ORMTable $ORMTable
     */
    public function __construct(ORMTable $ORMTable)
    {
        $this->ORMTable = $ORMTable;
        $this->tableName = $ORMTable->getTableName();
        $this->typesSQLDefinition();

        foreach ($this->ORMTable->getColumns() as $column) {
            $att = $column['columnName'];
            $this->columns[] = $att;
            $this->$att = null;
        }
    }

    use ORMConfigSQL;

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     * @throws ORMException
     */
    public function __set(string $name, $value): void
    {
        if (in_array($name, $this->columns)) {
            $this->$name = $value;
        } else {
            throw new ORMException("Il est impossible de modifier un élément qui n'existe pas dans la table.");
        }
    }

    public function constructWithStdclass(stdClass $class): void
    {
        foreach ($this->columns as $column) {
            $this->$column = $this->valuesType($this->ORMTable->getColumns()[$column]['columnType'], $class->$column);
        }
    }

    /**
     * @param string $type
     * @param string $value
     * @return DateTime|int|null|string
     */
    public function valuesType(string $type, string $value)
    {
        if (in_array($type, $this->sqlString)) {
            return htmlspecialchars($value);
        } elseif (in_array($type, $this->sqlNumeric)) {
            return (int)$value;
        } elseif (in_array($type, $this->sqlDate)) {
            return new DateTime($value);
        }
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     * @return ORMEntity
     */
    public function setTableName(string $tableName): ORMEntity
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @return ORMTable
     */
    public function getORMTable(): ORMTable
    {
        return $this->ORMTable;
    }
}
