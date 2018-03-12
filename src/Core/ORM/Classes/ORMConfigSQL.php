<?php

namespace Core\ORM\Classes;

/**
 * Trait pour définir les tableaux des types SQL disponibles afin de les avoir dans nos classes et pouvoir tester les éléments en entrée en sortie.
 * Trait ORMConfigSQL
 * @package Core\ORMController
 */
trait ORMConfigSQL
{
    /**
     * @var array
     */
    private $sqlTypes = [];
    /**
     * @var array
     */
    private $sqlString = [];
    /**
     * @var array
     */
    private $sqlNumeric = [];
    /**
     * @var array
     */
    private $sqlDate = [];
    /**
     * @var array
     */
    private $sqlSpatial = [];

    /**
     * Définit les tableaux
     */
    public function typesSQLDefinition(): void
    {
        $this->sqlNumeric = ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'float', 'double', 'real', 'bit', 'boolean', 'serial'];
        $this->sqlDate = ['date', 'datetime', 'timestamp', 'time', 'year'];
        $this->sqlString = ['char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'binary', 'varbinary', 'tinyblob', 'mediumblob', 'blob', 'longblob', 'enum', 'set'];
        $this->sqlSpatial = ['geometry', 'point', 'linestring', 'polygon', 'multipoint', 'multilinestring', 'multipolygon', 'geometrycollection'];
        $this->sqlTypes = array_merge(
            $this->sqlNumeric,
            $this->sqlDate,
            $this->sqlString,
            $this->sqlSpatial
        );
    }
}
