<?php
namespace App\Models;

class Friend
{
    private string $name;
    private string $surname;
    private string $createdAt;
    private ?int $id;
    private ?int $userId;
    private ?int $friendId;

    public function __construct(string $name, string $surname, string $createdAt, ?int $id, ?int $userId, ?int $friendId)
    {
        $this->name = $name;
        $this->surname = $surname;
        $this->createdAt = $createdAt;
        $this->id = $id;
        $this->userId = $userId;
        $this->friendId = $friendId;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function getSurname(): string
    {
        return $this->surname;
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

    public function getFriendId(): ?int
    {
        return $this->friendId;
    }
}
