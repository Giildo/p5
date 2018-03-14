<?php

namespace Core\ORM\Classes;

use Core\Model\Model;
use Core\ORM\Interfaces\ORMModelInterface;
use stdClass;

/**
 * Class ORMSelect
 * @package Core\ORM\Classes
 */
class ORMSelect
{
    /**
     * @var array
     */
    private $statement = [];

    /**
     * @var string
     */
    private $configFiles;

    /**
     * @var string
     */
    private $tableName;

    /**
     * ORMSelect constructor.
     * Initialise le fichier de config pour récupérer le mode de construction des modèles.
     * Initialise le "join" à "false"
     *
     * @uses $this->typesSQLDefinition()
     * Trait utilisé pour définir les tableaux qui serviront à la vérification des types SQL
     *
     * @param string $configFiles
     */
    public function __construct(string $configFiles)
    {
        $this->configFiles = $configFiles;
        $this->typesSQLDefinition();
        $this->statement['join'] = false;
    }

    use ORMConfigSQL;

    /**
     * Récupère le nom des colonnes qui seront récupérées dans la BDD.
     * Si non définies, met "*" pour tout récupérer.
     *
     * @param array|null $columnsName
     * @return ORMSelect
     */
    public function select(?array $columnsName = []): ORMSelect
    {
        $this->statement['select'] = $columnsName;

        return $this;
    }

    /**
     * Récupère le nom de la table principale qui sera appelée
     *
     * @param string $tableName
     * @return ORMSelect
     */
    public function from(string $tableName): ORMSelect
    {
        $this->tableName = $tableName;
        $this->statement['from'] = $tableName;

        return $this;
    }

    /**
     * Récupère les informations pour sélectionner la·es ligne·s dans la BDD
     * Dans un tableau avec en clé la colonne qui servira de filtre et en valeur la donnée de filtre
     * Ex. : ['id' => '4']
     *
     * @param array $whereOptions
     * @return ORMSelect
     */
    public function where(array $whereOptions): ORMSelect
    {
        $this->statement['where'] = $whereOptions;

        return $this;
    }

    /**
     * Récupère les informations liées au trie des données.
     * Récupère un tableau qui contient en clé la colonne qui servira de trie et en valeur le sens de trie
     * Ex. : ['id' => 'desc']
     *
     * @param array $orderByOptions
     * @return ORMSelect
     * @throws ORMException
     */
    public function orderBy(array $orderByOptions): ORMSelect
    {
        foreach ($orderByOptions as $direction) {
            $directionMaj = strtoupper($direction);
            if ($directionMaj === 'DESC' || $directionMaj === 'ASC') {
                $this->statement['orderBy'] = $orderByOptions;
            } else {
                throw new ORMException("\"{$direction}\" n'est pas un sens de trie autorisé, veuillez indiquer \"ASC\" ou \"DESC\".");
            }
        }

        return $this;
    }

    /**
     * Récupère les limitations pour la récupération des données
     * En premier paramètre la limite.
     * En second paramètre le départ pour le filtrage.
     * (5, 10) => récupérer les 5 premières lignes de la BDD à partir de la 10ème ligne (exclue).
     *
     * @param string $limit
     * @param null|string $offset
     * @return $this
     */
    public function limit(string $limit, ?string $offset = '0'): ORMSelect
    {
        $this->statement['limit'] = $limit;
        $this->statement['offset'] = $offset;

        return $this;
    }

    /**
     * Récupère les informations pour créer des jointures de type "INNER JOIN"
     * En premier paramètre la table qui servira de jointure
     * En second un tableau récupérant les liens de jointures
     * Ex : ('comments', ['posts.id' => 'comments.post']) va lier la table dans "from" avec la table "comments" en
     * utilisant les colonnes comme lien les colonnes "id" de "posts" et "post" de "comments".
     *
     * @param string $tableJoined
     * @param array $joinOptions
     * @return ORMSelect
     */
    public function innerJoin(string $tableJoined, array $joinOptions): ORMSelect
    {
        $joinParameters = [];
        $joinParameters['joinType'] = 'INNER JOIN';
        $this->join($joinParameters, $tableJoined, $joinOptions);

        $this->statement['joins'][] = $joinParameters;

        return $this;
    }

    /**
     * Récupère les informations pour créer des jointures de type "INNER JOIN"
     * En premier paramètre la table qui servira de jointure
     * En second un tableau récupérant les liens de jointures
     * Ex : ('comments', ['posts.id' => 'comments.post']) va lier la table dans "from" avec la table "comments" en
     * utilisant les colonnes comme lien les colonnes "id" de "posts" et "post" de "comments".
     *
     * @param string $tableJoined
     * @param array $joinOptions
     * @return ORMSelect
     */
    public function rightJoin(string $tableJoined, array $joinOptions): ORMSelect
    {
        $joinParameters = [];
        $joinParameters['joinType'] = 'RIGHT JOIN';
        $this->join($joinParameters, $tableJoined, $joinOptions);

        $this->statement['joins'][] = $joinParameters;

        return $this;
    }

    /**
     * @param string $tableJoined
     * @param array $joinOptions
     * @return ORMSelect
     */
    public function leftJoin(string $tableJoined, array $joinOptions): ORMSelect
    {
        $joinParameters = [];
        $joinParameters['joinType'] = 'LEFT JOIN';
        $this->join($joinParameters, $tableJoined, $joinOptions);

        $this->statement['joins'][] = $joinParameters;

        return $this;
    }

    /**
     * @param array $entitiesLinked
     * @param array $columnsLinks
     * @param string $relationType
     * @return ORMSelect
     */
    public function insertEntity(array $entitiesLinked, array $columnsLinks, string $relationType): ORMSelect
    {
        $innerOptions = [];
        $innerOptions['insertEntity'] = $entitiesLinked;
        $innerOptions['columnsLinks'] = $columnsLinks;
        $innerOptions['relationType'] = $relationType;

        $this->statement['insertEntity'][] = $innerOptions;

        return $this;
    }

    /**
     *
     * @uses $this->statementDefinition()
     * Récupère les différentes informations insérées dans les tableaux de l'objet, pour créer un statement.
     *
     * @uses $this->ormAndEntitiesCreation()
     * Crée :
     *  ->  les ORMTables qui seront insérés dans les entités qu'on va crée plus loin, car ils sont nécessaire à la méthode
     *      ORMEntity->constructWithStdClass().
     *  ->  les entités dont les noms sont tirés de l'appel à "From" et de l'appel à "join". Ex : utilisation de
     *      ORMSelect->from('posts'), la méthode va crée un objet de type 'Post' de 'Entity'.
     *
     * @uses ORMModel->ORMFind()
     * Fonction appelée depuis le modèle pour récupérer les informations depuis la base de données sous forme de StdClass
     * Données récupérées à partir du statement crée plus haut.
     *
     * @uses $this->constructEntityWithStdClass()
     * Utilisation de la méthode ORMEntity->constructWithStdClass pour créer les objets à partir des informations récupérées
     * juste au-dessus.
     *
     * @uses $this->insertEntityIntoAnotherEntity()
     * Va insérer un ensemble d'entités enfants dans l'entité parent ou une entité enfant dans un ensemble d'entités parents,
     * selon $this->statement['innerOption'] qui détermine l'entité enfant et l'entité parent,
     * et l'option $this->statement['onToMany'] qui détermine le type de relation.
     *
     * @uses $this->resetSelect()
     * Juste avant de renvoyer les données, reset tout ce qui se trouve dans les tableaux du statement pour pouvoir
     * réutiliser ORMSelect tout de suite après sans avoir à créer une nouvelle instance.
     *
     * @param ORMModelInterface[] $models
     * @return ORMEntity|ORMEntity[]
     * @throws ORMException
     */
    public function execute(ORMModelInterface  ...$models)
    {
        $statement = '';
        $this->statementDefinition($statement);

        $entityList = require $this->configFiles;

        $ormTables = [];
        $entities = [];
        $this->ormAndEntitiesCreation($ormTables, $models, $entities, $entityList);

        $items = (isset($this->statement['where'])) ?
            $models[0]->ORMFind($statement, null, $this->statement['where']) :
            $models[0]->ORMFind($statement);

        $allEntities = $this->constructEntityWithStdClass($items, $entities, $ormTables);

        if (isset($this->statement['insertEntity'])) {
            $result = $this->insertEntityIntoAnotherEntity($allEntities);
            var_dump($result);
            $this->resetSelect();
            return $result;
        }

        $this->resetSelect();

        return $allEntities;
    }

    /**
     * @param string $statement
     * @throws ORMException
     */
    private function statementDefinition(string &$statement): void
    {
        $statement = 'SELECT ';

        if (!empty($this->statement['select'])) {
            $end = $this->count($this->statement['select']);
            $i = 0;

            foreach ($this->statement['select'] as $entity => $columns) {
                foreach ($columns as $column) {
                    $i++;

                    $statement .= $entity . '.' . $column . ' as ' . $entity . '_' . $column;

                    if ($i < $end) {
                        $statement .= ', ';
                    } elseif ($i === $end) {
                        $statement .= ' ';
                    }
                }
            }
        } else {
            $statement .= '* ';
        }

        if (isset($this->statement['from'])) {
            $statement .= 'FROM ' . $this->statement['from'];
        } else {
            throw new ORMException("La table n'a pas été définie.");
        }

        if ($this->statement['join']) {
            foreach ($this->statement['joins'] as $join) {
                $statement .= ' ' . strtoupper($join['joinType']);
                $statement .= ' ' . $join['tableJoined'];
                foreach ($join['joinOptions'] as $leftId => $rightId) {
                    $statement .= ' ON ' . $leftId . ' = ' . $rightId;
                }
            }
        }

        if (isset($this->statement['where'])) {
            foreach ($this->statement['where'] as $key => $value) {
                if (preg_match('#\.#', $key)) {
                    $result = explode('.', $key);
                    $key2 = ':' . $result[0] . ucfirst(strtolower($result[1]));
                } else {
                    $key2 = ':' . $key;
                }
                $statement .= ' WHERE ' . $key . "=" . $key2;
            }
        }

        if (isset($this->statement['orderBy'])) {
            foreach ($this->statement['orderBy'] as $key => $value) {
                $statement .= ' ORDER BY ' . $key . ' ' . strtoupper($value);
            }
        }

        if (isset($this->statement['limit'])) {
            $statement .= ' LIMIT ' . $this->statement['limit'];
            $statement .= (isset($this->statement['offset'])) ? ' OFFSET ' . $this->statement['offset'] : '';
        }
    }

    private function join(array &$joinParameters, string $tableJoined, array $joinOptions): void
    {
        $this->statement['join'] = true;
        $joinParameters['tableJoined'] = $tableJoined;
        $joinParameters['joinOptions'] = $joinOptions;
    }

    /**
     * Emboite les entités les unes dans les autres. A besoin de deux paramètres :
     * - Quelle entité s'emboite dans l'autre, ex. : $this->statement['insertEntity'] = ['comments' => 'posts']
     * - Si la relation est :
     * -> une pour plusieurs (OneToMany), ex. on insère une catégorie (Category) dans plusieurs articles (posts)
     * -> plusieurs pour une (ManyToOne), ex. on insère tous les commentaires (comments) dans l'article avec lequel ils sont liés (posts)
     *
     * Dans les deux types de relation, on sépare dans "$allEntities" les entités récupérées dans $this->statement['insertEntity']
     * dans le tableau ou la variable "$entitiesChild" et "$entitiesParent".
     *
     * Dans le cas de la relation "OneToMany" on parcourt le tableau "$entitiesParents" pour y ajouter l'entité enfant.
     * Dans le cas de la relation "ManyToOne" on insère le tableau "$entitiesChild" dans l'entité parent.
     *
     * @param array $allEntities
     * @return mixed
     * -> relation OneToMany : ORMEntity[]
     * -> relation ManyToOne : ORMEntity
     */
    private function insertEntityIntoAnotherEntity(array $allEntities)
    {
        //Récupère les noms des entités à stocker les unes dans les autres
        $entitiesLinked = [];
        foreach ($this->statement['insertEntity'] as $options) {
            foreach ($options['insertEntity'] as $entity1 => $entity2) {
                if (!in_array($entity1, $entitiesLinked)) {
                    $entitiesLinked[] = $entity1;
                }

                if (!in_array($entity2, $entitiesLinked)) {
                    $entitiesLinked[] = $entity2;
                }
            }
        }

        //Classe les entités par nom d'entité dans un tableau associatif
        $entitiesStocked = [];
        foreach ($allEntities as $entity) {
            foreach ($entitiesLinked as $entityName) {
                if ($entity->getTableName() === $entityName) {
                    $entitiesStocked[$entityName][] = $entity;
                }
            }
        }

        //Parcours les options entrées dans le tableau d'options de la classe
        foreach ($this->statement['insertEntity'] as $options) {
            //Vérifie les types de relations
            if ($options['relationType'] === 'oneToMany') {
                //Récupère dans le tableau d'option qui est le fils et qui est le parent
                foreach ($options['insertEntity'] as $child => $parent) {
                    $entityChild = $entitiesStocked[$child][0];

                    $provisonialStock = [];

                    //Parcours le tableau des entités stockés et rangés plus haut dans la fonction
                    foreach ($entitiesStocked[$parent] as $entityParent) {
                        $att = 'set' . ucfirst($entityChild->getTableName());
                        $attSingular = substr($att, 0, -1);
                        $attSingularIrregular = str_replace('ies', 'y', $att);
                        if (is_callable([$entityParent, $att])) {
                            $entityParent->$att($entityChild);
                        } elseif (is_callable([$entityParent, $attSingular])) {
                            $entityParent->$attSingular($entityChild);
                        } elseif (is_callable([$entityParent, $attSingularIrregular])) {
                            $entityParent->$attSingularIrregular($entityChild);
                        }
                        $provisonialStock[] = $entityParent;
                    }

                    $entitiesStocked[$parent] = $provisonialStock;
                    unset($entitiesStocked[$child]);
                }
            } elseif ($options['relationType'] === 'manyToOne') {
                foreach ($options['insertEntity'] as $child => $parent) {
                    $entityParent = $entitiesStocked[$parent][0];

                    $provisonialStock = [];

                    foreach ($entitiesStocked[$child] as $entityChild) {
                        $provisonialStock[] = $entityChild;
                    }

                    $att = 'set' . ucfirst($provisonialStock[0]->getTableName());
                    $attSingular = substr($att, 0, -1);
                    $attSingularIrregular = str_replace('ies', 'y', $att);
                    if (is_callable([$entityParent, $att])) {
                        $entityParent->$att($provisonialStock);
                    } elseif (is_callable([$entityParent, $attSingular])) {
                        $entityParent->$attSingular($provisonialStock);
                    } elseif (is_callable([$entityParent, $attSingularIrregular])) {
                        $entityParent->$attSingularIrregular($provisonialStock);
                    }

                    $entitiesStocked[$parent] = [];
                    $entitiesStocked[$parent] = $entityParent;
                    unset($entitiesStocked[$child]);
                }
            } elseif ($options['relationType'] === 'manyToMany') {
                foreach ($options['insertEntity'] as $child => $parent) {
                    foreach ($options['columnsLinks'] as $c => $p) {
                        $childColumn = 'get' . ucfirst($c);
                        $parentColumn = 'get' . ucfirst($p) . 'Id';
                    }

                    $provisonialStock = [];

                    foreach ($entitiesStocked[$parent] as $entityParent) {
                        foreach ($entitiesStocked[$child] as $entityChild) {
                            if ($entityChild->$childColumn() === $entityParent->$parentColumn()) {
                                $att = 'set' . ucfirst($entityChild->getTableName());
                                $attSingular = substr($att, 0, -1);
                                $attSingularIrregular = str_replace('ies', 'y', $att);
                                if (is_callable([$entityParent, $att])) {
                                    $entityParent->$att($entityChild);
                                } elseif (is_callable([$entityParent, $attSingular])) {
                                    $entityParent->$attSingular($entityChild);
                                } elseif (is_callable([$entityParent, $attSingularIrregular])) {
                                    $entityParent->$attSingularIrregular($entityChild);
                                }

                                $provisonialStock[] = $entityParent;
                            }
                        }
                    }


                    $entitiesStocked[$parent] = [];
                    $entitiesStocked[$parent] = $provisonialStock;
                    unset($entitiesStocked[$child]);
                }
            }
        }

        foreach ($entitiesStocked as $index => $entities) {
            return $entities;
        }
    }

    private function resetSelect(): void
    {
        $this->statement = [];
        $this->statement['join'] = false;
    }

    /**
     * @param array $items
     * @param array $entities
     * @param array $ormTables
     * @return ORMEntity[]
     * @throws ORMException
     */
    private function constructEntityWithStdClass(array $items, array $entities, array $ormTables): array
    {
        $stdClasses = [];
        $allStdClasses = [];
        $allEntities = [];

        if ($this->statement['join']) {
            foreach ($items as $item) {
                foreach ($entities as $key => $entity) {
                    $stdClasses[$key] = new stdClass();
                }
                foreach ($item as $columnName => $value) {
                    $results = explode('_', $columnName);

                    $stdClass = $stdClasses[$results[0]];

                    $att = $results[1];
                    $stdClass->$att = $value;

                    $stdClasses[$results[0]] = $stdClass;
                }
                $allStdClasses[] = $stdClasses;
            }

            $allEntities = [];
            foreach ($allStdClasses as $stdClasses) {
                foreach ($stdClasses as $entityName => $stdClass) {
                    $entity = $entities[$entityName];
                    /** @var ORMEntity $entityItem */
                    $entityItem = new $entity($ormTables[$entityName]);
                    $entityItem->constructWithStdclass(
                        $stdClass,
                        ['sqlString' => $this->sqlString, 'sqlDate' => $this->sqlDate, 'sqlNumeric' => $this->sqlNumeric]
                    );

                    if (!in_array($entityItem, $allEntities)) {
                        $allEntities[] = $entityItem;
                    }
                }
            }
        } else {
            foreach ($items as $stdClass) {
                $entity = $entities[$this->tableName];
                /** @var ORMEntity $entityItem */
                $entityItem = new $entity($ormTables[$this->tableName]);
                $entityItem->constructWithStdclass(
                    $stdClass,
                    ['sqlString' => $this->sqlString, 'sqlDate' => $this->sqlDate, 'sqlNumeric' => $this->sqlNumeric]
                );

                $allEntities[] = $entityItem;
            }
        }

        return $allEntities;
    }

    /**
     * @param array $ormTables
     * @param array $models
     * @param array $entities
     * @param array $entityList
     * @throws ORMException
     */
    private function ormAndEntitiesCreation(array &$ormTables, array $models, array &$entities, array $entityList): void
    {
        //Crée les différents ORMTables
        /** @var Model $model */
        foreach ($models as $model) {
            $ormTable = new ORMTable($model->getTable());
            $ormTable->constructWithStdclass($model->ORMShowColumns());
            $ormTables[$model->getTable()] = $ormTable;
        }

        //Crée les différentes entités
        foreach ($entityList as $entityName => $entityPath) {
            if ($this->tableName === $entityName) {
                $entities[$entityName] = new $entityPath();
            }
            if ($this->statement['join']) {
                foreach ($this->statement['joins'] as $join) {
                    if ($join['tableJoined'] === $entityName) {
                        $entities[$entityName] = new $entityPath();
                    }
                }
            }
        }

        //Renvoie une erreur si le tableau des entités est vide
        if (empty($entities)) {
            throw new ORMException("L'entité n'a pas été trouvée dans le fichier de configuration \"{$this->configFiles}\".");
        }
    }

    /**
     * @param $select
     * @return int
     */
    private function count($select): int
    {
        $i = 0;

        foreach ($select as $entity => $columns) {
            foreach ($columns as $column) {
                $i++;
            }
        }

        return $i;
    }
}
