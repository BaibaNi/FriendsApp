<?php

namespace App\Controllers;

use App\Database;
use App\Exceptions\FormValidationException;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\UserAlreadyLikedException;
use App\Models\Author;
use App\Models\Comment;
use App\Models\UserProfile;
use App\Redirect;
use App\Validation\Errors;
use App\View;
use App\Models\Article;
use Doctrine\DBAL\Exception;
use App\Validation\ArticleFormValidator;

class ArticlesController extends Database
{

    /** @throws Exception */
    public function index(): View
    {
        $stmt = Database::connection()
            ->prepare('SELECT * FROM articles order by created_at desc');
        $articlesList = $stmt->executeQuery()->fetchAllAssociative();

        $articles = [];
        foreach ($articlesList as $item){
            $articles[] = new Article(
                $item['title'],
                $item['description'],
                $item['created_at'],
                $item['id'],
                $item['user_id']
            );
        }

        $stmt2 = Database::connection()
            ->prepare('SELECT users.id, user_profiles.name, user_profiles.surname FROM users JOIN user_profiles ON
    (users.id = user_profiles.user_id)');

        $authorsList = $stmt2->executeQuery()->fetchAllAssociative();


        $authors = [];
        foreach ($authorsList as $item2){
            $authors[] = new Author(
                $item2['name'],
                $item2['surname'],
                (int)$item2['id']
            );
        }

        return new View('Articles/index', [
                'articles' => $articles,
                'authors' => $authors
            ]
        );

    }


    /** @throws Exception */
    public function show(array $vars): View
    {
        $articleQuery = Database::connection()
            ->prepare('SELECT * FROM articles where id = ?');
        $articleQuery->bindValue(1, $vars['id']);
        $list = $articleQuery->executeQuery()->fetchAssociative(); //fetchAllAssociative()[0]

        // todo check if it is not null, then build object

        $article = new Article(
            $list['title'],
            $list['description'],
            $list['created_at'],
            $list['id'],
            $list['user_id']
        );


        $authorQuery = Database::connection()
            ->prepare('SELECT users.id, user_profiles.name, user_profiles.surname FROM users JOIN user_profiles ON 
    (users.id = user_profiles.user_id) where user_id = ?');
        $authorQuery->bindValue(1, $list['user_id']);
        $list2 = $authorQuery->executeQuery()->fetchAssociative();

        $author = new Author(
            $list2['name'],
            $list2['surname'],
            $list2['id']
        );


        $articleLikesQuery = Database::connection()
            ->prepare('SELECT * from article_likes where article_id = ?');
        $articleLikesQuery->bindValue(1, (int) $vars['id']);
        $articleLikes = $articleLikesQuery->executeQuery()->fetchAllAssociative();


        $userLikeQuery = Database::connection()
            ->prepare('SELECT * from article_likes where user_id = ? and article_id = ?');
        $userLikeQuery->bindValue(1, $_SESSION['userid']);
        $userLikeQuery->bindValue(2, (int) $vars['id']);
        $userLikes = $userLikeQuery->executeQuery()->fetchAssociative();

        $userLike = count($userLikes['id']);


        $commentsQuery = Database::connection()
            ->prepare('SELECT * from article_comments where article_id = ? order by created_at desc');
        $commentsQuery->bindValue(1, (int) $vars['id']);
        $commentsList = $commentsQuery->executeQuery()->fetchAllAssociative();

        $comments = [];
        foreach($commentsList as $comment){

            $userCommentsQuery = Database::connection()
                ->prepare('SELECT * FROM user_profiles where user_id = ?');
            $userCommentsQuery->bindValue(1, $comment['user_id']);
            $userProfile = $userCommentsQuery->executeQuery()->fetchAssociative();

            $comments[] = new Comment(
                $userProfile['name'],
                $userProfile['surname'],
                $comment['description'],
                $comment['created_at'],
                $comment['id'],
                $userProfile['user_id']
            );

        }

        return new View('Articles/show', [
            'article' => $article,
            'author' => $author,
            'userLike' => $userLike,
            'articleLikes' => count($articleLikes),
            'comments' => $comments
        ]);
    }



    public function create(): View
    {
        return new View('Articles/create', [
            'errors' => Errors::getAll(),
            'inputs' => $_SESSION['inputs'] ?? []
        ]);
    }



    /** @throws Exception */
    public function store(): Redirect
    {
        $validator = new ArticleFormValidator($_POST, [
            'title' => ['required', 'min:3'],
            'description' => ['required']
        ]);
        try{
            $validator->passes();

            Database::connection()
                ->insert('articles', [
                    'user_id' => $_SESSION['userid'],
                    'title' => $_POST['title'],
                    'description' => $_POST['description'],
                ]);

            return new Redirect('/articles'); // if not returning Redirect as object, then use: header('Location: /articles');

        } catch(FormValidationException $exception){
            $_SESSION['errors'] = $validator->getErrors();
            $_SESSION['inputs'] = $_POST;
            return new Redirect('/articles/create');
        }
    }


    /** @throws Exception  */
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
        //todo error messages with validation(?)
        try {
            $stmt = Database::connection()
                ->prepare('SELECT * FROM articles where id = ?');
            $stmt->bindValue(1, $vars['id']);
            $list = $stmt->executeQuery()->fetchAssociative();

            if (!$list) {
                throw new ResourceNotFoundException("Article with id {$vars['id']} not found.");
            }

            $article = new Article(
                $list['title'],
                $list['description'],
                $list['created_at'],
                $list['id']
            );

            return new View('Articles/edit', [
                'article' => $article
            ]);
        } catch (ResourceNotFoundException $exception){
            var_dump($exception->getMessage());
            return new View('404');
        }
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


    /** @throws Exception */
    public function like(array $vars): Redirect
    {
        $articleID = (int) $vars['id'];
        $userId = $_SESSION['userid'];

        try{
            $stmt = Database::connection()
                ->prepare('SELECT user_id from article_likes where article_id = ?');
            $stmt->bindValue(1, (int) $vars['id']);
            $userLikes = $stmt->executeQuery()->fetchAllAssociative();


            if(!empty($userLikes)){
                foreach ($userLikes as $userLike){
                    if($userLike['user_id'] === $userId){
                        //todo
                        throw new UserAlreadyLikedException("You have already voted for this article.");
                    }
                }
            }


            Database::connection()
                ->insert('article_likes', [
                    'article_id' => $articleID,
                    'user_id' => $_SESSION['userid']
                ]);

            return new Redirect("/articles/{$articleID}");

        } catch(UserAlreadyLikedException $exception) {
//            var_dump($exception->getMessage());
            return new Redirect("/articles/{$articleID}");
        }
    }


    /** @throws Exception */
    public function dislike(array $vars): Redirect
    {
        $status = null;
        $articleID = (int) $vars['id'];
        $userId = $_SESSION['userid'];

        try{
            $stmt = Database::connection()
                ->prepare('SELECT id, user_id from article_likes where article_id = ?');
            $stmt->bindValue(1, (int) $vars['id']);
            $userLikes = $stmt->executeQuery()->fetchAllAssociative();

            if(!empty($userLikes)){
                foreach ($userLikes as $userLike){
                    if($userLike['user_id'] === $userId){

                        Database::connection()
                            ->delete('article_likes', [
                                'id' => $userLike['id']
                            ]);

                        $status = new Redirect("/articles/{$articleID}");
                    }
                }
            } else{
                //todo
                throw new UserAlreadyLikedException("Article does not have votes.");
            }
        } catch(UserAlreadyLikedException $exception) {
            //todo
//            var_dump($exception->getMessage());
            $status = new Redirect("/articles/{$articleID}");
        }

        return $status;
    }

}
