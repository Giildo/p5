<?php

namespace Core\ORM\Interfaces;

interface ORMModelInterface
{
    /**
     * Récupère un statement pour ajouter un élément dans la base de données
     *
     * @param string $statement
     * @return void
     */
    public function ORMInsert(string $statement): void;

    /**
     * Récupère un statement pour modifier un élément dans la base de données
     *
     * @param string $statement
     * @param string $primaryKey
     * @param $primaryKeyValue
     * @return void
     */
    public function ORMUpdate(string $statement, string $primaryKey, $primaryKeyValue): void;

    /**
     * Récupère les colonnes dans la base de données et les retourne
     *
     * @return array
     */
    public function ORMShowColumns(): array;

    /**
     * Crée une table dans la base de données
     *
     * @param string $statement
     * @return void
     */
    public function ORMCreateTable(string $statement): void;

    /**
     * @param string $statement
     * @param null|string $entityType
     * @param array|null $whereOptions
     * @param bool|null $inOption
     * @return array
     */
    public function ORMFind(
        string $statement,
        ?string $entityType = null,
        ?array $whereOptions = [],
        ?bool $inOption = false
    ): array;
}
