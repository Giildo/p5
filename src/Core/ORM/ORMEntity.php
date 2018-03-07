<?php

namespace Core\ORM;

class ORMEntity
{
    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * ORMEntity constructor.
     * @param string $tableName
     */
    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * @param string $name
     * @param string $value
     * @return void
     */
    public function __set(string $name, string $value): void
    {
        $this->$name = $value;
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
}
