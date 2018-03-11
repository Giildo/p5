<?php

namespace App\Entity;

use Core\Entity\Entity;
use Core\Entity\EntityInterface;
use Core\ORM\ORMTable;

class News extends Entity implements EntityInterface
{
    /**
     * @var string
     */
    protected $tableName = 'news';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $content;

    /**
     * News constructor.
     * @param ORMTable $ORMTable
     * @throws \Core\ORM\ORMException
     */
    public function __construct(ORMTable $ORMTable)
    {
        parent::__construct($ORMTable);
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
