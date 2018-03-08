<?php

namespace Core\ORM;

use stdClass;

class ORMTable
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
     * ORMTable constructor.
     * @param string $tableName
     */
    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * @param string $columnName
     * @param string $columnType
     * @param int|null $max
     * @param array|null $options
     * @return ORMTable
     */
    public function hasColumn(string $columnName, string $columnType, ?int $max = null, ?array $options = []): ORMTable
    {
        $this->columns[$columnName] = [
            'columnName' => $columnName,
            'columnType' => $columnType,
            'max' => $max,
            'options' => $options
        ];

        return $this;
    }

    /**
     * @param stdClass[] $classes
     */
    public function constructWithStdclass(array $classes)
    {
        foreach ($classes as $class) {
            $name = $class->Field;

            $typeAndMax = $this->typeDefinition($class->Type);
            $type = $typeAndMax[0];
            $max = (isset($typeAndMax[1])) ? $typeAndMax[1] : null;

            $options = $this->optionsDefinition($class);

            $this->hasColumn($name, $type, (int)$max, $options);
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
     * @return ORMTable
     */
    public function setTableName(string $tableName): ORMTable
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param array $columns
     */
    public function setColumns(array $columns): void
    {
        $this->columns = $columns;
    }

    /**
     * Récupère la définition de la colonne et sépare le type du max
     *
     * @param string $type
     * @return string[]
     */
    protected function typeDefinition(string $type): array
    {
        $type = str_replace(')', '', $type);
        return explode('(', $type);
    }

    protected function optionsDefinition(Stdclass $class): array
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
