<?php

namespace App\Models;

class Article
{
    private string $title;
    private string $description;
    private string $createdAt;
    private ?int $id = null;
    private ?int $userId = null;


    public function __construct(string $title, string $description, string $createdAt, ?int $id = null, ?int $userId = null)
    {
        $this->title = $title;
        $this->description = $description;
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->userId = $userId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }
}