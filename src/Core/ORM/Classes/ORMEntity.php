<?php

namespace Core\ORM\Classes;

use DateTime;
use stdClass;

class ORMEntity
{
    /**
     * @var string
     */
    protected $tableName = '';

    /**
     * @var ORMTable
     */
    protected $ORMTable;

    /**
     * @var array
     */
    protected $primaryKey = [];

    /**
     * ORMEntity constructor.
     * @param ORMTable|null $ORMTable
     * @param bool|null $SQLTypeDefine
     * @throws ORMException
     */
    public function __construct(?ORMTable $ORMTable = null, ?bool $SQLTypeDefine = false)
    {
        if (!is_null($ORMTable)) {
            $this->setORMTable($ORMTable);
        }

        if ($SQLTypeDefine) {
            $this->typesSQLDefinition();
        }

        date_default_timezone_set('Europe/Paris');
    }

    /**
     * @param $name
     * @param $value
     * @throws ORMException
     */
    public function __set($name, $value)
    {
        $method = 'set' . ucfirst($name);

        if (is_callable([$this, $method])) {
            if (!is_array($value) && !is_object($value)) {
                $this->$method(htmlspecialchars($value));
            } else {
                $this->$method($value);
            }
        } else {
            if (array_key_exists($name, $this->ORMTable->getColumns())) {
                throw new ORMException("Vous n'avez pas l'autorisation de modifier la propriété \"{$name}\".");
            } else {
                throw new ORMException("La propriété \"{$name}\" n'existe pas \"{$this->tableName}\".");
            }
        }
    }

    /**
     *
     * @param string $name
     * @throws ORMException
     * @return mixed
     */
    public function __get(string $name)
    {
        $method = 'get' . ucfirst($name);

        if (is_callable([$this, $method])) {
            return static::$method();
        } else {
            if (array_key_exists($name, $this->ORMTable->getColumns())) {
                throw new ORMException("Vous n'avez pas l'autorisation d'accéder à la propriété \"{$name}\".");
            } else {
                throw new ORMException("La propriété \"{$name}\" n'existe pas dans l'objet \"{$this->tableName}\".");
            }
        }
    }

    use ORMConfigSQL;

    /**
     * Utilise les éléments récupérés dans un stdClass pour créer un objet avec des propriétés avec le bon typage
     * Différencie si l'élément est une foreign key, si c'est le cas le place dans "colonne + Id"
     *
     * @uses valuesType
     * Récupère le typage dans l'ORMTable et la valeur à modifier
     * Renvoie la valeur avec le bon typage
     *
     * @param stdClass $class
     * @param array|null $sqlTypes
     * @throws ORMException
     */
    public function constructWithStdclass(stdClass $class, ?array $sqlTypes = []): void
    {
        foreach ($class as $key => $value) {
            if (!is_null($key)) {
                $keySet = 'set' . ucfirst($key);
                if ($this->ORMTable->getColumns()[$key]['options']['foreign']) {
                    $property = $keySet . 'Id';
                    $this->$property($this->valuesType($this->ORMTable->getColumns()[$key]['columnType'], $value, $sqlTypes));
                } else {
                    $this->$keySet($this->valuesType($this->ORMTable->getColumns()[$key]['columnType'], $value, $sqlTypes));
                }

                if ($this->ORMTable->getColumns()[$key]['options']['primary']) {
                    $this->primaryKey[] = $key;
                }
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
     * Vérifie que l'ORMTable correspond selon le nom et l'ajoute dans les paramètres
     *
     * @param ORMTable $ORMTable
     * @throws ORMException
     */
    public function setORMTable(ORMTable $ORMTable): void
    {
        if ($this->tableName === $ORMTable->getTableName()) {
            $this->ORMTable = $ORMTable;
        } else {
            throw new ORMException("La Table \"{$ORMTable->getTableName()}\" passée en argument ne correspond par à l'entité créée.");
        }
    }

    /**
     * @param array $primaryKey
     */
    public function setPrimaryKey(array $primaryKey): void
    {
        $this->primaryKey = $primaryKey;
    }

    /**
     * @param string $type
     * @param string $value
     * @param array|null $sqlTypes
     * @return DateTime|int|null|string
     * @throws ORMException
     */
    private function valuesType(string $type, string $value, ?array $sqlTypes = [])
    {
        if (!empty($sqlTypes)) {
            if (in_array($type, $sqlTypes['sqlString'])) {
                return htmlspecialchars($value);
            } elseif (in_array($type, $sqlTypes['sqlNumeric'])) {
                return (int)$value;
            } elseif (in_array($type, $sqlTypes['sqlDate'])) {
                return new DateTime($value);
            } else {
                throw new ORMException("Le typage \"{$type}\" n'existe pas.");
            }
        } else {
            if (in_array($type, $this->sqlString)) {
                return htmlspecialchars($value);
            } elseif (in_array($type, $this->sqlNumeric)) {
                return (int)$value;
            } elseif (in_array($type, $this->sqlDate)) {
                return new DateTime($value);
            } else {
                throw new ORMException("Le typage \"{$type}\" n'existe pas.");
            }
        }
    }
}
