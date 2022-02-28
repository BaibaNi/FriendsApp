<?php
namespace App\Models;

class UserProfile extends User
{
    private string $name;
    private string $surname;
    private string $birthday;

    public function __construct(string $name, string $surname, string $birthday, string $email, string $password, string $createdAt, ?int $id)
    {
        parent::__construct($email, $password, $createdAt, $id);
        $this->name = $name;
        $this->surname = $surname;
        $this->birthday = $birthday;
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function getSurname(): string
    {
        return $this->surname;
    }


    public function getBirthday(): string
    {
        return $this->birthday;
    }
}