<?php
namespace App\Models;

class Comment
{
    private string $name;
    private string $surname;
    private string $comment;
    private string $createdAt;
    private ?int $id;
    private ?int $userId;

    public function __construct(string $name, string $surname, string $comment, string $createdAt, ?int $id = null, ?int $userId = null)
    {
        $this->name = $name;
        $this->surname = $surname;
        $this->comment = $comment;
        $this->createdAt = $createdAt;
        $this->id = $id;
        $this->userId = $userId;
    }



    public function getName(): string
    {
        return $this->name;
    }


    public function getSurname(): string
    {
        return $this->surname;
    }


    public function getComment(): string
    {
        return $this->comment;
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