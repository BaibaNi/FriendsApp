<?php
namespace App\Models;

class User
{
    private string $email;
    private string $password;
    private string $createdAt;
    private ?int $id;


    public function __construct(string $email, string $password, string $createdAt, ?int $id = null)
    {
        $this->email = $email;
        $this->password = $password;
        $this->createdAt = $createdAt;
        $this->id = $id;
    }


    public function getEmail(): string
    {
        return $this->email;
    }


    public function getPassword(): string
    {
        return $this->password;
    }


    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }


    public function getId(): ?int
    {
        return $this->id;
    }
}