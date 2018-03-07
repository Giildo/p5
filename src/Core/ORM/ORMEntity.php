<?php

namespace Core\ORM;

use stdClass;

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
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, $value): void
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
     * @param stdClass $class
     */
    public function constructWithStdclass(stdClass $class)
    {
        $name = $class->Field;

        $typeAndMax = $this->typeDefinition($class->Type);
        $type = $typeAndMax[0];
        $max = (isset($typeAndMax[1])) ? $typeAndMax[1] : null;

        $options = $this->optionsDefinition($class);

        $this->hasColumn($name, $type, (int)$max, $options);
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

    /**
     * @param array $columns
     * @return ORMEntity
     */
    public function setColumns(array $columns): ORMEntity
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Récupère la définition de la colonne et sépare le type du max
     *
     * @param string $type
     * @return string[]
     */
    private function typeDefinition(string $type): array
    {
        $type = str_replace(')', '', $type);
        return explode('(', $type);
    }

    private function optionsDefinition(Stdclass $class): array
    {
        $options = [];

        if ($class->Null === 'YES') {
            $options['not_null'] = false;
        }

        if ($class->Key === 'PRI') {
            $options['primary'] = true;
        } elseif ($class->Key === 'MUL') {
            $options['foreign'] = true;
        }

        if ($class->Extra === 'auto_increment') {
            $options['auto_increment'] = true;
        }

        return $options;
    }
}
