<?php

namespace App\Controllers;

use Doctrine\DBAL\DriverManager;
use App\Models\Article;
use App\View;

class UsersController
{

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function index(): View // RESTful API
    {
        $sql = 'SELECT * FROM mini_fb.articles';
        $stmt = $this->dbConnect()->prepare($sql);
        $list = $stmt->executeQuery()->fetchAllAssociative();

        $articles = [];
        foreach ($list as $item){
            $articles[] = new Article($item['title'], $item['description']);
        }

        return new View('Users/index.html', [
                'articles' => $articles
            ]
        );

    }


    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function show(array $vars): View
    {
        $sql = 'SELECT * FROM mini_fb.articles where id = ?';
        $stmt = $this->dbConnect()->prepare($sql);
        $stmt->bindValue(1, $vars['id']);
        $list = $stmt->executeQuery()->fetchAllAssociative()[0];

        $title = $list['title'];
        $description = $list['description'];

        $article = new Article($title, $description);

        return new View('Users/show.html', [
            'id' => $vars['id'],
            'title' => $article->getTitle(),
            'description' => $article->getDescription()
        ]);
    }


    private function dbConnect()
    {
        $connectionParams = [
            'dbname' => 'mini_fb',
            'user' => 'banibai',
            'password' => 'Learning_mysql_074',
            'host' => 'localhost',
            'driver' => 'pdo_mysql'
        ];

        try {
            return DriverManager::getConnection($connectionParams);
        } catch (\Doctrine\DBAL\Exception $e) {
            echo 'Error! ' . $e->getMessage() . PHP_EOL;
            die();
        }
    }

}
