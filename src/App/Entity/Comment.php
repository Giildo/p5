<?php

namespace App\Entity;

use Core\Entity\Entity;
use Core\Entity\EntityInterface;
use DateTime;

class Comment extends Entity implements EntityInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $comment;

    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @var DateTime
     */
    protected $updatedAt;

    /**
     * @var int
     */
    protected $userId;

    /**
     * @var int
     */
    protected $postId;

    /**
     * @var string
     */
    protected $user;

    /***
     * @var string
     */
    protected $post;

    public function __construct()
    {
        $this->date(['updatedAt', 'createdAt']);
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
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param bool|null $datetime
     * @param null|string $returnFormat
     * @return DateTime|string
     */
    public function getCreatedAt(?bool $datetime = true, ?string $returnFormat = 'Le %e %b %Y à %Hh%Mmin')
    {
        return $this->getDate($this->createdAt, $datetime, $returnFormat);
    }

    /**
     * @param bool|null $datetime
     * @param null|string $returnFormat
     * @return DateTime
     */
    public function getUpdatedAt(?bool $datetime = true, ?string $returnFormat = 'Le %e %b %Y à %Hh%Mmin')
    {
        return $this->getDate($this->updatedAt, $datetime, $returnFormat);
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return int
     */
    public function getPostId(): int
    {
        return $this->postId;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPost(): string
    {
        return $this->post;
    }
}
