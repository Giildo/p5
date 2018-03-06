<?php

namespace Core\ORM;

use PDO;

abstract class ORMEntity
{
    /**
     * @var string
     */
    protected $tableName = null;

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @param string $name
     * @param string $value
     * @return void
     */
    public function __set(string $name, string $value): void
    {
        $method = 'set' . ucfirst($name);

        if (is_callable([$this, $method])) {
            $this->$method($value);
        }
    }

    /**
     * @param string $columnName
     * @param string $columnType
     * @param int|null $max
     * @param array|null $options
     * @return ORMEntity
     */
    public function hasColumn(string $columnName, string $columnType, ?int $max = null, ?array $options = []): ORMEntity
    {
        $this->columns[] = [
            'columnName' => $columnName,
            'columnType' => $columnType,
            'max' => $max,
            'options' => $options
        ];

        return $this;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
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

    protected function columnsDefinition()
    {
        $columns = [];

        foreach ($this->columns as $column) {
            if ($column['columnName'] !== 'id') {
                $columns[] .= $column['columnName'];
            }
        }

        return implode(', ', $columns);
    }

    protected function valuesDefinition(string $columns): string
    {
        $columns = explode(', ', $columns);
        $values = [];

        foreach ($columns as $column) {
            $method = 'get' . ucfirst($column);
            $values[] = '"' . $this->$method() . '"';
        }

        return implode(', ', $values);
    }
}
