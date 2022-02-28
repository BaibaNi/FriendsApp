<?php

namespace App\Controllers;

use App\Database;
use App\Models\Author;
use App\Models\UserProfile;
use App\Redirect;
use App\View;
use App\Models\Article;
use Doctrine\DBAL\Exception;

class ArticlesController extends Database
{

    /**
     * @throws Exception
     */
    public function index(): View
    {
        $stmt = Database::connection()
            ->prepare('SELECT * FROM articles order by created_at desc');
        $articlesList = $stmt->executeQuery()->fetchAllAssociative();

        $articles = [];
        foreach ($articlesList as $item){
            $articles[] = new Article($item['title'], $item['description'], $item['created_at'], $item['id'], $item['user_id']);
        }

        $stmt2 = Database::connection()
            ->prepare('SELECT users.id, user_profiles.name, user_profiles.surname FROM users JOIN user_profiles ON
    (users.id = user_profiles.user_id)');

        $authorsList = $stmt2->executeQuery()->fetchAllAssociative();


        $authors = [];
        foreach ($authorsList as $item2){
            $authors[] = new Author($item2['name'], $item2['surname'], (int)$item2['id']);
        }

        return new View('Articles/index', [
                'articles' => $articles,
                'authors' => $authors
            ]
        );

    }


    /**
     * @throws Exception
     */
    public function show(array $vars): View
    {
        $stmt = Database::connection()
            ->prepare('SELECT * FROM articles where id = ?');
        $stmt->bindValue(1, $vars['id']);
        $list = $stmt->executeQuery()->fetchAssociative(); //fetchAllAssociative()[0]

        // todo check if it is not null, then build object

        $article = new Article($list['title'], $list['description'], $list['created_at'], $list['id'], $list['user_id']);

        $stmt2 = Database::connection()
            ->prepare('SELECT users.id, user_profiles.name, user_profiles.surname FROM users JOIN user_profiles ON 
    (users.id = user_profiles.user_id) where user_id = ?');
        $stmt2->bindValue(1, $list['user_id']);
        $list2 = $stmt2->executeQuery()->fetchAssociative();

        $author = new Author($list2['name'], $list2['surname'], $list2['id']);

        return new View('Articles/show', [
            'article' => $article,
            'author' => $author
        ]);
    }


    public function create(): View
    {
        return new View('Articles/create');
    }

    /**
     * @throws Exception
     */
    public function store(): Redirect
    {
        //todo Validate form, if fields filled etc.

        Database::connection()
            ->insert('articles', [
                'user_id' => $_SESSION['userid'],
                'title' => $_POST['title'],
                'description' => $_POST['description'],
            ]);

        return new Redirect('/articles'); // if not returning Redirect as object, then use: header('Location: /articles');
    }


    /**
     * @throws Exception
     */
    public function delete(array $vars): Redirect
    {
        Database::connection()
            ->delete('articles', [
                'id' => (int)$vars['id'],
            ]);

        return new Redirect('/articles');
    }


    /** @throws Exception */
    public function edit(array $vars): View
    {
        $sql = 'SELECT * FROM articles where id = ?';
        $stmt = Database::connection()->prepare($sql);
        $stmt->bindValue(1, $vars['id']);
        $list = $stmt->executeQuery()->fetchAssociative(); //fetchAllAssociative()[0]

        // todo check if it is not null, then build object

        $article = new Article($list['title'], $list['description'], $list['created_at'], $list['id']);

        return new View('Articles/edit', [
            'article' => $article
        ]);
    }


    /** @throws Exception */
    public function update(array $vars): Redirect
    {
        Database::connection()
            ->update('articles', [
                    'title' => $_POST['title'],
                    'description' => $_POST['description'],
                ], [
                    'id' => (int)$vars['id'],
                ]
            );

        return new Redirect('/articles/' . (int)$vars['id']);
    }


}
