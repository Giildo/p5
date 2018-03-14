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
     *
     * @param array $columnsName
     * @return ORMSelect
     */
    public function select(array $columnsName): ORMSelect
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
     * Indique qu'on attend en retour un élément unique.
     *
     * @return ORMSelect
     */
    public function singleItem(): ORMSelect
    {
        $this->statement['singleItem'] = true;

        return $this;
    }

    /**
     * Indique qu'on attend un nombre de résultat et non des entités.
     *
     * @return ORMSelect
     */
    public function countColumns(): ORMSelect
    {
        $this->statement['count'] = true;

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

            return $this->return($result);
        }

        return $this->return($allEntities);
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
            throw new ORMException("Les colonnes à récupérer n'ont pas été définies.");
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
     *      -> une pour plusieurs (OneToMany), ex. on insère une catégorie (Category) dans plusieurs articles (posts)
     *      -> plusieurs pour une (ManyToOne), ex. on insère tous les commentaires (comments) dans l'article avec
     *         lequel ils sont liés (posts)
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
        $entitiesLinked = $this->searchEntitiesLinked();

        //Classe les entités par nom d'entité dans un tableau associatif
        $entitiesStocked = $this->sortEntities($allEntities, $entitiesLinked);

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
                        $this->addEntity($entityChild, $entityParent);

                        $provisonialStock[] = $entityParent;
                    }

                    $this->storeEntities($entitiesStocked, $parent, $provisonialStock, $child);
                }
            } elseif ($options['relationType'] === 'oneToOne') {
                foreach ($options['insertEntity'] as $child => $parent) {
                    $entityParent = $entitiesStocked[$parent][0];
                    $entityChild = $entitiesStocked[$child][0];

                    $provisonialStock = [];

                    $this->addEntity($entityChild, $entityParent);

                    $provisonialStock[] = $entityParent;

                    $this->storeEntities($entitiesStocked, $parent, $provisonialStock, $child);
                }
            } elseif ($options['relationType'] === 'manyToOne') {
                foreach ($options['insertEntity'] as $child => $parent) {
                    $entityParent = $entitiesStocked[$parent][0];

                    $provisonialStock = [];

                    foreach ($entitiesStocked[$child] as $entityChild) {
                        $provisonialStock[] = $entityChild;
                    }

                    $this->addEntity($provisonialStock[0], $entityParent, false, $provisonialStock);

                    $this->storeEntities($entitiesStocked, $parent, $entityParent, $child);
                }
            } elseif ($options['relationType'] === 'manyToMany') {
                $childColumn = $parentColumn = '';
                foreach ($options['insertEntity'] as $child => $parent) {
                    foreach ($options['columnsLinks'] as $c => $p) {
                        $childColumn = 'get' . ucfirst($c);
                        $parentColumn = 'get' . ucfirst($p) . 'Id';
                    }

                    $provisonialStock = [];

                    foreach ($entitiesStocked[$parent] as $entityParent) {
                        foreach ($entitiesStocked[$child] as $entityChild) {
                            if ($entityChild->$childColumn() === $entityParent->$parentColumn()) {
                                $this->addEntity($entityChild, $entityParent);

                                $provisonialStock[] = $entityParent;
                            }
                        }
                    }

                    $this->storeEntities($entitiesStocked, $parent, $provisonialStock, $child);
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

    /**
     * Récupère les noms des tables qui vont être insérées les unes dans les autres,
     * les insère dans un tableau et le retourne.
     *
     * @return array
     */
    private function searchEntitiesLinked(): array
    {
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

        return $entitiesLinked;
    }

    /**
     * Récupère toutes les entités récupérées en paramètre de la méthode, les classes par type d'entité,
     * les stock par type dans un tableau et renvoie ce tableau
     *
     * @param array $allEntities
     * @param array $entitiesLinked
     * @return array
     */
    private function sortEntities(array $allEntities, array $entitiesLinked): array
    {
        $entitiesStocked = [];

        foreach ($allEntities as $entity) {
            foreach ($entitiesLinked as $entityName) {
                if ($entity->getTableName() === $entityName) {
                    $entitiesStocked[$entityName][] = $entity;
                }
            }
        }

        return $entitiesStocked;
    }

    /**
     * Créer une fonction de type setter à partir du nom de l'entité enfant. Pour prendre tous les cas possibles (pluriel,
     * singulier et pluriel irrégulier), création de trois variables différentes. On teste ensuit les trois variables
     * au niveau de la classe pour vérifier si elle existe, si c'est le cas, on l'utilse.
     * - $att => pluriel : ORMEntity posts->setComments car il peut y avoir plusieurs commentaire dans un post
     * - $attSingular => singulier : ORMEntity posts->setUser car les posts ne peuvent avoir qu'un auteur
     * - $attSingularIrregulier => singulier pour les pluriels irréguliers : ORMEntity posts->setCategory car il un post
     * ne peut avoir qu'une seul catégorie et que category = categories
     *
     * Prise en compte des relation oneToMany ou ManyToOne ou ManyToMany avec le paramètre $addEntity. Ajout d'une entité
     * simple ou d'un array selon le type de relation.
     *
     * @param ORMEntity $entityChild
     * @param ORMEntity $entityParent
     * @param bool|null $addEntity
     * @param array $entitiesChild
     */
    private function addEntity(ORMEntity $entityChild, ORMEntity &$entityParent, ?bool $addEntity = true, ?array $entitiesChild = [])
    {
        $att = 'set' . ucfirst($entityChild->getTableName());
        $attSingular = substr($att, 0, -1);
        $attSingularIrregular = str_replace('ies', 'y', $att);

        if (is_callable([$entityParent, $att])) {
            ($addEntity) ? $entityParent->$att($entityChild) : $entityParent->$att($entitiesChild);
        } elseif (is_callable([$entityParent, $attSingular])) {
            ($addEntity) ? $entityParent->$attSingular($entityChild) : $entityParent->$attSingular($entitiesChild);
        } elseif (is_callable([$entityParent, $attSingularIrregular])) {
            ($addEntity) ? $entityParent->$attSingularIrregular($entityChild) : $entityParent->$attSingularIrregular($entitiesChild);

        }
    }

    /**
     * Récupère les options pour savoir si on attend un élément unique et si on veut le nombre de colonnes.
     * Réinitialise le statement pour pouvoir réutiliser ORMSelect tout de suite après sans le ré-instancier.
     * Retourne :
     * - int : Si $this->statement['count'] est à true
     * - ORMEntity : Si $this->statement['singleItem'] est à true
     * - ORMEntity[] : Si les deux sont à false
     * @param array $items
     * @return int|ORMEntity|ORMEntity[]
     */
    private function return(array $items)
    {
        $singleItem = $this->statement['singleItem'];
        $count = $this->statement['count'];

        $this->resetSelect();

        if ($count) {
            return count($items);
        } else {
            return ($singleItem) ? $items[0] : $items;
        }
    }

    /**
     * Récupère en référence le tableau qui stocke les entités de manière ordonnées.
     * Réinitialise la partie qui concerne le stockage de l'entité parente pour éviter les doublons.
     * Insère l'entité ou les entités parentes qu'on a complété·e·s.
     * Supprime la partie avec les entités enfants qui ont été insérées dans les entités parentes.
     *
     * @param array $entitiesStock
     * @param string $parentName
     * @param ORMEntity|ORMEntity[] $itemsToStore
     * @param string $childName
     */
    private function storeEntities(array &$entitiesStock, string $parentName, $itemsToStore, string $childName)
    {
        $entitiesStock[$parentName] = [];
        $entitiesStock[$parentName] = $itemsToStore;
        unset($entitiesStock[$childName]);
    }
}
