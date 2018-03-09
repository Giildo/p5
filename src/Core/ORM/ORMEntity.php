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
    protected $properties = [];

    /**
     * @var array
     */
    protected $primaryKey = [];

    /**
     * @var array
     */
    protected $foreignKey = [];

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
            if ($column['options']['primary']) {
                $this->primaryKey[$column['columnName']] = null;
            } elseif ($column['options']['foreign']) {
                $this->foreignKey[$column['columnName']] = null;
            } else {
                $this->properties[$att] = null;
            }
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
        $type = gettype($value);
        if (array_key_exists($name, $this->properties)) {
            if (gettype($this->properties[$name]) === 'NULL' || $type === gettype($this->properties[$name])) {
                $this->properties[$name] = $value;
            } else {
                throw new ORMException("Il est impossible de modifier le type de la propriété.");
            }
        } elseif (array_key_exists($name, $this->foreignKey)) {
            if (gettype($this->properties[$name]) === 'NULL' || $type === gettype($this->foreignKey[$name])) {
                $this->foreignKey[$name] = $value;
            } else {
                throw new ORMException("Il est impossible de modifier le type de la propriété.");
            }
        } elseif (array_key_exists($name, $this->primaryKey)) {
            throw new ORMException("Il est impossible de modifier la clé primaire de l'objet.");
        } else {
            throw new ORMException("Il est impossible de modifier un élément qui n'existe pas dans la table \"{$this->tableName}\".");
        }
    }

    public function __get(string $name)
    {
        $values = array_merge($this->foreignKey, $this->properties, $this->primaryKey);
        return $values[$name];
    }

    public function constructWithStdclass(stdClass $class): void
    {
        foreach ($class as $key => $value) {
            if (array_key_exists($key, $this->primaryKey)) {
                $this->primaryKey[$key] = $this->valuesType($this->ORMTable->getColumns()[$key]['columnType'], $class->$key);
            } elseif (array_key_exists($key, $this->foreignKey)) {
                $this->foreignKey[$key] = $this->valuesType($this->ORMTable->getColumns()[$key]['columnType'], $class->$key);
            } else {
                $this->properties[$key] = $this->valuesType($this->ORMTable->getColumns()[$key]['columnType'], $class->$key);
            }
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

    /**
     * @return array
     */
    public function getPrimaryKey(): array
    {
        return $this->primaryKey;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return array
     */
    public function getForeignKey(): array
    {
        return $this->foreignKey;
    }

    /**
     * @param string $type
     * @param string $value
     * @return DateTime|int|null|string
     */
    private function valuesType(string $type, string $value)
    {
        if (in_array($type, $this->sqlString)) {
            return htmlspecialchars($value);
        } elseif (in_array($type, $this->sqlNumeric)) {
            return (int)$value;
        } elseif (in_array($type, $this->sqlDate)) {
            return new DateTime($value);
        }
    }
}
