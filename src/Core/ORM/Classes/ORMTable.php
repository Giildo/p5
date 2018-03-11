<?php

namespace Core\ORM\Classes;

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
     * Ajoute une colonne à la définition de la table.
     * Pour les options, prend en compte :
     * - primary => true
     * - auto_increment => true
     * - not_null => false (par défault en true)
     *
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
     * Crée une colonne en fonction d'un élément stdClass
     *
     * @uses typeDefinition
     * Récupère le type de la colonne (ex. : varchar(50))
     * Sépare le typage du maximum de caractère
     * Renvoie un tableau pour séparer les deux
     *
     * @uses optionsDefinition
     * Trois colonnes sont utilisées dans les stdClass pour définir différentes options
     * Lit les différentes colonnes et définit les options en fonction
     * Renvoie le tableau d'options
     *
     * @uses hasColumn
     * Crée la colonne en fonction des éléments récupérés en amont.
     *
     * @param stdClass[] $classes
     */
    public function constructWithStdclass(array $classes)
    {
        foreach ($classes as $class) {
            $name = $class->Field;

            $type = '';
            $max = 0;
            $this->typeDefinition($class->Type, $type, $max);

            $options = $this->optionsDefinition($class);

            $this->hasColumn($name, $type, $max, $options);
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
     * Fait passer par référence les deux valeurs
     *
     * @param string $columnType
     * @param string $type
     * @param int $max
     * @return void
     */
    protected function typeDefinition(string $columnType, string &$type, int &$max): void
    {
        $columnType = str_replace(')', '', $columnType);
        $columnType = explode('(', $columnType);
        $type = $columnType[0];
        $max = (isset($columnType[1])) ? (int)$columnType[1] : null;
    }

    /**
     * Trois colonnes sont utilisées dans les stdClass pour définir différentes options
     * Lit les différentes colonnes et définit les options en fonction
     * Renvoie le tableau d'options
     *
     * @param stdClass $class
     * @return array
     */
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
