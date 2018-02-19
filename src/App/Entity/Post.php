<?php

namespace App\Entity;

use Core\Entity\EntityInterface;
use DateTime;

/**
 * Class Post
 * @package App\Entity
 */
class Post implements EntityInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $category;

    /**
     * @var DateTime
     */
    private $createdAt;

    /**
     * @var DateTime
     */
    private $updatedAt;

    /**
     * @var int|string
     */
    private $user;

    /**
     * Post constructor.
     */
    public function __construct()
    {
        $this->date();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Génère les dates avec l'objet DateTime
     */
    private function date(): void
    {
        $this->createdAt = new DateTime($this->createdAt);
        $this->updatedAt = new DateTime($this->updatedAt);
    }

    /**
     * @return int|string
     */
    public function getUser()
    {
        return $this->user;
    }
}
