<?php

namespace App\Entity;

use Core\Entity\Entity;
use Core\Entity\EntityInterface;

class Admin extends Entity implements EntityInterface
{
    /**
     * @var string
     */
    protected $tableName = 'admin';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
