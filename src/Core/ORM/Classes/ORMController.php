<?php

namespace Core\ORM\Classes;

use Core\Model\Model;
use Core\ORM\Interfaces\ORMModelInterface;
use DateTime;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use PDO;

class ORMController
{
    /**
     * ORMController constructor
     */
    public function __construct()
    {
        $this->typesSQLDefinition();
    }

    use ORMConfigSQL;

    /**
     * Récupère les éléments d'un objet ORMController pour créer une nouvelle table dans la BDD
     *
     * @param ORMTable $ORMTable
     * @param ORMModelInterface $model
     * @return void
     * @throws ORMException
     */
    public function createTable(ORMTable $ORMTable, ORMModelInterface $model): void
    {
        $statement = '(';
        $end = count($ORMTable->getColumns());
        $i = 1;

        foreach ($ORMTable->getColumns() as $column) {
            foreach ($column as $key => $value) {
                switch ($key) {
                    case 'columnName':
                        $statement .= $value;
                        break;

                    case 'columnType':
                        $this->columnTypeDefinition($statement, $value);
                        break;

                    case 'max':
                        $statement .= (!is_null($value)) ? "({$value})" : '';
                        break;

                    case 'options':
                        $this->optionsDefinition($statement, $value);
                        break;
                }
            }

            //Vérifie si c'est la dernière instance du tableau ou non
            if ($i === $end) {
                $statement .= ')';
            } else {
                $statement .= ', ';
            }

            $i++;
        }

        $this->verifStatement($statement);

        $model->ORMCreateTable($ORMTable->getTableName(), $statement);
    }

    /**
     * Vérifie si une clé primaire est définie, ce qui indique que l'objet a été récupéré depuis la BDD
     * Si c'est le cas il va lancer la fonction ORMUpdate pour modifier une ligne en BDD
     * Sinon lance ORMInsert pour ajouter une nouvelle ligne
     *
     * @param ORMEntity $entity
     * @param ORMModelInterface $model
     * @throws ORMException
     */
    public function save(ORMEntity $entity, ORMModelInterface $model): void
    {
        //Récupère la définition des colonnes depuis la BDD
        $columnsBDD = $model->ORMShowColumns();

        //Vérifie que toutes les colonnes sont définies sinon renvoie une erreur
        $this->verifAllColumnsIsDefined($entity->getORMTable()->getColumns(), $columnsBDD, $entity);

        $primaryDefined = false;
        foreach ($entity->getPrimaryKey() as $primaryKey) {
            if (!is_null($entity->$primaryKey)) {
                $primaryDefined = true;
            }
        }

        if ($primaryDefined) {
            $this->update($entity, $model, $columnsBDD);
        } else {
            $this->insert($entity, $model, $columnsBDD);
        }
    }

    public function delete(ORMEntity $entity, ORMModelInterface $model): void
    {
        $model->ORMDelete($entity);
    }

    /**
     * Récupère l'entité de type ORMEntity qu'il va devoir sauvegarder
     * Lance la fonction columnsDefinition() pour créer les colonnes qui se trouve dans l'ORMEntity et récupérer les valeurs
     * --> cette dernière vérifie également que les valeurs correspondent à la définition des colonnes
     * Vérifie ensuite que les colonnes existe dans la Table
     * Si tout est OK enregistre l'objet dans la table
     *
     * @param ORMEntity $entity
     * @param ORMModelInterface $model
     * @param array $columnsBDD
     * @return void
     * @throws ORMException
     */
    private function insert(ORMEntity $entity, ORMModelInterface $model, array $columnsBDD): void
    {
        //Définie les colonnes et les valeurs à rentrer
        $columns = $values = '';
        $this->columnsDefinition($entity->getORMTable()->getColumns(), $entity, $columnsBDD, $columns, $values);

        //Envoie au modèle le statement nécessaire à l'insertion
        $model->ORMInsert("INSERT INTO {$entity->getTableName()} ({$columns}) VALUES ({$values})");
    }

    /**
     * @param ORMEntity $entity
     * @param ORMModelInterface $model
     * @param array $columnsBDD
     * @return void
     * @throws ORMException
     */
    private function update(ORMEntity $entity, ORMModelInterface $model, array $columnsBDD): void
    {
        $columns = $values = '';
        $columnsAndValues = $this->columnsDefinition($entity->getORMTable()->getColumns(), $entity, $columnsBDD, $columns, $values);

        $statement = "UPDATE {$entity->getTableName()} SET ";
        for ($i = 0 ; $i < count($columnsAndValues[0]) ; $i++) {
            if ($i !== 0) {
                $statement .= ', ';
            }

            $statement .= $columnsAndValues[0][$i] . '=' . $columnsAndValues[1][$i];
        }

        $columnPrimary = $entity->getPrimaryKey()[0];
        $statement .= " WHERE {$columnPrimary}=:{$columnPrimary}";

        $model->ORMUpdate($statement, $columnPrimary, $entity->$columnPrimary);
    }

    /**
     * Récupère les colonnes de l'ORMEntity
     * Vérifie que la colonne appelée n'est pas id et si n'est pas le cas ajoute le nom de la colonne dans $columnArray
     * Appelle valuesDefinition() pour récupérer les informations dans l'ORMEntity
     * Place la string partir de $columnArray dans $columnString
     *
     * @param array $columns
     * @param ORMEntity $entity
     * @param array $columnsBDD
     * @param string $columnsString
     * @param string $valuesString
     * @return array
     * @throws ORMException
     */
    private function columnsDefinition(array $columns, ORMEntity $entity, array $columnsBDD, string &$columnsString, string &$valuesString): array
    {
        $columnsEntity = [];

        //Parcours les colonnes de l'entité et les ajoute toute dans un tableau vide, sauf l'ID
        foreach ($columns as $column) {
            if ($column['columnName'] !== 'id') {
                $columnsEntity[] = $column['columnName'];
            }
        }

        $values = $this->valuesDefinition($columnsEntity, $entity, $columnsBDD, $valuesString);

        $columnsString = implode(', ', $columnsEntity);

        $columnsAndValues = [];
        $columnsAndValues[] = $columnsEntity;
        $columnsAndValues[] = $values;

        return $columnsAndValues;
    }

    /**
     * Récupère les colones créées lors de la fonction columnsDefinition()
     * Récupère la valeur dans l'objet stocké dans l'objet
     *
     * @param array $columnsEntity
     * @param ORMEntity $entity
     * @param array $columnsBDD
     * @param string $valuesString
     * @return array
     * @throws ORMException
     */
    private function valuesDefinition(array $columnsEntity, ORMEntity $entity, array $columnsBDD, string &$valuesString): array
    {
        $values = [];

        foreach ($columnsEntity as $columnEntity) {
            $typeArray = [];

            foreach ($columnsBDD as $columnTable) {
                if (strtolower($columnTable->Field) === strtolower($columnEntity)) {
                    $typeColumn = str_replace(')', '', $columnTable->Type);

                    $typeArray = explode('(', $typeColumn);

                    if ($columnTable->Key === 'MUL') {
                        $columnEntity = $columnEntity . 'Id';
                    }
                    break;
                }
            }

            //Vérifie si $typeArray est vide, si c'est le cas ça veut dire que la colonne n'est pas définie dans la base de données
            if (!empty($typeArray)) {
                //Vérifie la valeur, si OK la renvoie en forçant le type (par sécurité) et en échappant les caractères HTML (sécurité pour les string)
                $result = $this->verifValue($typeArray, $entity->$columnEntity, $columnEntity);

                if (is_null($result)) {
                    throw new ORMException("La valeur de la colonne \"{$columnEntity}\" ne correspond pas au type inscrit dans la base de données");
                } else {
                    $values[] = $result;
                }
            } else {
                throw new ORMException("La colonne \"{$columnEntity}\" n'est pas définie dans la base de données");
            }
        }

        $valuesString = implode(', ', $values);

        return $values;
    }

    /**
     * Récupère toutes les colonnes de l'ORMEntity, vérifie si elles ont une valeur et les ajoute à un tableau
     * Récupère toutes les colonnes de la table et les ajoute à un tableau
     * Compare les deux tableaux et si des éléments de la Table ne se trouvent pas dans l'ORMEntity renvoie une erreur
     *
     * @param array $columnsEntity Colonnes récupérées dans l'ORMEntity à sauvegarder
     * @param array $columnsBDD Colonnes récupérées dans la Table correspondant à l'ORMEntity
     * @param ORMEntity $entity
     * @return void
     * @throws ORMException
     */
    private function verifAllColumnsIsDefined(array $columnsEntity, array $columnsBDD, $entity): void
    {
        //Vérifie toutes dans le tableau créé ci-dessus si une valeur est rentrée, si c'est le cas, ajoute dans un tableau le nom de la colonne
        foreach ($columnsEntity as $columnEntity) {
            $att = $columnEntity['columnName'];

            //Ajoute "Id" à la fin du nom de la colonne si c'est une clé étrangère
            if ($columnEntity['options']['foreign']) {
                $att = $att . 'Id';
            }

            if ($att === 'id' || !is_null($entity->$att)) {
                $columnsEntityName[] = $columnEntity['columnName'];
            }
        }

        //Récupère toutes les colonnes qui sont notées "NOT NULL" dans la base de données
        foreach ($columnsBDD as $columnBDD) {
            if ($columnBDD->Null === 'NO') {
                $columnsResultsNotNull[] = $columnBDD->Field;
            }
        }

        //Compare les deux tableaux, au moins toutes les colonnes notées "NOT NULL" doivent être définies, sinon renvoie une erreur
        if (!empty($result = array_diff($columnsResultsNotNull, $columnsEntityName))) {
            $columns = implode(', ', $result);

            throw new ORMException("La/Les colonne(s) \"{$columns}\" ne doivent pas être vides");
        }
    }

    /**
     * Vérifie si le type récupéré dans le value existe dans la liste des types de SQL définie dans la config
     * Si ce n'est pas le cas renvoie une exception
     * Sinon ajoute au statement le type en majuscule
     *
     * @param string $statement
     * @param string $value
     * @throws ORMException
     * @return void
     */
    private function columnTypeDefinition(string &$statement, string $value): void
    {
        if (in_array(strtolower($value), $this->sqlTypes)) {
            $statement .= ' ' . strtoupper($value);
        } else {
            throw new ORMException('Le type SQL demandé n\'est pas valide');
        }
    }

    /**
     * Récupère le tableau d'option :
     * - Vérifie qu'il n'est pas vide
     * - S'il n'est pas vide, le parcours avec clé et valeur
     * - Si la valeur (qui est un booléen) est à true continue
     * Récupère la clé et selon celle-ci inscrira ce qu'il faut dans la ligne
     *
     * @param string $statement
     * @param array $options
     * @return void
     */
    private function optionsDefinition(string &$statement, array $options): void
    {
        $notNull = true;

        if (!empty($options)) {
            foreach ($options as $key => $value) {
                $statement .= ($key !== 'not_null') ? ' ' : '';
                switch ($key) {
                    case 'auto_increment':
                        $statement .= 'AUTO_INCREMENT';
                        break;

                    case 'primary':
                        $statement .= 'PRIMARY KEY';
                        break;

                    case 'not_null':
                        $notNull = false;
                        break;
                }
            }
        }

        $statement .= ($notNull) ? ' NOT NULL' : '';
    }

    /**
     * Récupère le statement et vérifie :
     * Qu'il n'y a pas plus d'une clé primaire
     * Qu'il y a au moins une clé primaire
     * --> Sinon renvoie des ORMException
     *
     * @param string $statement
     * @throws ORMException
     * @return void
     */
    private function verifStatement(string $statement): void
    {
        $result = preg_match_all('#PRIMARY KEY#', $statement);

        if ($result > 1) {
            throw new ORMException('La table ne doit pas contenir plus d\'une clé primaire !');
        } elseif ($result <= 0 || $result === false) {
            throw new ORMException('La table doit contenir au moins une clé primaire !');
        }
    }

    /**
     * Vérifie si la valeur à placer est du bon type par rapport au type demandé par la base de données
     *
     * @param array $valueTypeAndSize De la base de données
     * @param mixed $value Valeur à vérifier
     * @param string $columnName
     * @return mixed
     * @throws ORMException
     */
    private function verifValue(array $valueTypeAndSize, $value, string $columnName)
    {
        if (in_array($valueTypeAndSize[0], $this->sqlString)) {
            //Vérifie si une longueur maximale de texte est donnée, si oui vérifie si on est inférieur
            if (isset($valueTypeAndSize[1])) {
                if (strlen($value) <= $valueTypeAndSize[1]) {
                    return (is_string($value)) ? '"' . htmlspecialchars($value) . '"' : null;
                } else {
                    throw new ORMException("La longueur du texte contenu dans \"{$columnName}\" est trop importante");
                }
            } else {
                return (is_string($value)) ? '"' . htmlspecialchars($value) . '"' : null;
            }
        } elseif (in_array($valueTypeAndSize[0], $this->sqlNumeric)) {
            return (is_numeric($value)) ? (int)$value : null;
        } elseif (in_array($valueTypeAndSize[0], $this->sqlDate)) {
            return ($value instanceof DateTime) ? $value->format('"Y-m-d H:i:s"') : null;
        }

        throw new ORMException("Le format de \"{$columnName}\" n'existe pas");
    }
}
