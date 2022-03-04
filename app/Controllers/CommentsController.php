<?php

namespace App\Controllers;

use App\Database;
use App\Exceptions\CommentValidationException;
use App\Exceptions\ResourceNotFoundException;
use App\Models\Article;
use App\Models\Author;
use App\Models\Comment;
use App\Redirect;
use App\Validation\CommentFormValidator;
use App\Validation\Errors;
use App\View;
use Doctrine\DBAL\Exception;

class CommentsController extends Database
{

    /** @throws \Doctrine\DBAL\Exception */
    public function comment(array $vars): View
    {
        $articleQuery = Database::connection()
            ->prepare('SELECT * FROM articles where id = ?');
        $articleQuery->bindValue(1, $vars['id']);
        $list = $articleQuery->executeQuery()->fetchAssociative(); //fetchAllAssociative()[0]

//        if(count($list) === 0){
//            //todo
//            var_dump('No data');
//        }

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

        return new View("Articles/show", [
            'article' => $article,
            'author' => $author,
            'userLike' => $userLike,
            'articleLikes' => count($articleLikes),
            'comments' => $comments,
            'errors' => Errors::getAll(),
            'inputs' => $_SESSION['inputs'] ?? []
        ]);
    }



    public function addComment(array $vars): Redirect
    {
        $validator = new CommentFormValidator($_POST, [
            'description' => ['required', 'Min:3']
        ]);
        try {
            $validator->passes();

            Database::connection()
                ->insert('article_comments', [
                    'article_id' => $vars['id'],
                    'user_id' => $_SESSION['userid'],
                    'description' => $_POST['description']
                ]);

            return new Redirect("/articles/{$vars['id']}");
        } catch(CommentValidationException $exception){
            $_SESSION['errors'] = $validator->getErrors();
            $_SESSION['inputs'] = $_POST;
            return new Redirect("/articles/{$vars['id']}/comment");
        }
    }


    /** @throws Exception  */
    public function delete(array $vars): Redirect
    {

        Database::connection()
            ->delete('article_comments', [
                'id' => (int)$vars['commentid'],
            ]);

        return new Redirect("/articles/{$vars['id']}");
    }

}