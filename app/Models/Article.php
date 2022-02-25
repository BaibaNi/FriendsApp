<?php

namespace App\Models;

class Article
{
    private string $title;
    private string $description;

    // kā linki, kur uzklikšķinot, ir title un description
    public function __construct(string $title, string $description)
    {
        $this->title = $title;
        $this->description = $description;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}