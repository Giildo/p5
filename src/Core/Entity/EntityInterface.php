<?php

namespace Core\Entity;

use Jojotique\ORM\Classes\ORMTable;

interface EntityInterface
{
    /**
     * EntityInterface constructor.
     * @param ORMTable $ORMTable
     */
    public function __construct(ORMTable $ORMTable);
}
