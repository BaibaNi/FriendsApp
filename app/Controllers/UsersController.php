<?php

namespace App\Controllers;

use App\Database;
use App\Redirect;
use App\View;
use App\Models\User;
use App\Models\UserProfile;
use Doctrine\DBAL\Exception;


class UsersController extends Database
{

//---USER DISPLAY---
    /** @throws Exception */
    public function index(): View
    {
        $list = Database::connection()
            ->prepare('SELECT * FROM users')
            ->executeQuery()
            ->fetchAllAssociative();

        $users = [];
        foreach ($list as $item){
            $users[] = new User($item['email'], $item['password'], $item['created_at'], $item['id']);
        }

        return new View('Users/index', [
                'users' => $users
            ]
        );
    }


    /** @throws Exception */
    public function show(array $vars): View
    {
        $stmt1 = Database::connection()
            ->prepare('SELECT * FROM users where id = ?');
        $stmt1->bindValue(1, $vars['id']);
        $userList = $stmt1->executeQuery()->fetchAssociative();


        $stmt2 = Database::connection()
            ->prepare('SELECT * FROM user_profiles where user_id = ?');
        $stmt2->bindValue(1, $vars['id']);
        $userProfile = $stmt2->executeQuery()->fetchAssociative();


        $user = new UserProfile($userProfile['name'], $userProfile['surname'], $userProfile['birthday'],
            $userList['email'], $userList['password'], $userList['created_at'], $userList['id']);

        return new View('Users/show', [
            'user' => $user
        ]);
    }


//---USER REGISTRATION---
    public function getRegister(): View
    {
        return new View('Users/register');
    }

    /** @throws Exception */
    public function register(): Redirect
    {
        //todo Validate form, if fields filled etc. repeat password

        Database::connection()
            ->insert('users', [
                'email' => $_POST['email'],
                'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
            ]);

        $res = Database::connection()
            ->prepare('SELECT * FROM users WHERE id = LAST_INSERT_ID()')
            ->executeQuery()
            ->fetchAssociative();


        Database::connection()
            ->insert('user_profiles', [
                'user_id' => (int)$res['id'],
                'name' => $_POST['name'],
                'surname' => $_POST['surname'],
                'birthday' => $_POST['birthday'],
            ]);

        return new Redirect('/users');
    }
}
