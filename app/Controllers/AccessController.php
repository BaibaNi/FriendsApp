<?php
namespace App\Controllers;

use App\Database;
use App\Redirect;
use App\View;
use Doctrine\DBAL\Exception;

class AccessController extends Database
{
//---MAIN VIEW---
    public function index(): View
    {
        return new View('Main/index');
    }

//---LOGIN---

    public function getLogin(): View
    {
        return new View('Users/login');
    }


    /** @throws Exception */
    public function login(): Redirect
    {
        $status = null;
//        if(isset($_POST['submit'])) {

            $userEmail = $_POST['email'];
            $userPassword = $_POST['password'];

            $stmt = Database::connection()
                ->prepare('SELECT * FROM users WHERE email = ?'); // AND password = ?
            $stmt->bindValue(1, $userEmail);
//            $stmt->bindValue(2, $userPassword);
            $user = $stmt->executeQuery()->fetchAllAssociative();

            if(count($user) === 0){
                //todo
                var_dump("User not found!");
            } else{
                $hashedPassword = $user[0]['password'];
                if(password_verify($userPassword, $hashedPassword) ){

                    $stmt = Database::connection()
                        ->prepare('SELECT * FROM user_profiles WHERE user_id = ?');
                    $stmt->bindValue(1, $user[0]["id"]);
                    $userLogged = $stmt->executeQuery()->fetchAssociative();

                    session_start();
                    $_SESSION['userid'] = $user[0]["id"];
                    $_SESSION['username'] = $userLogged["name"];

                    $status = new Redirect('/');

                } else{
                    //todo
                    var_dump("Email or password is not correct!");
                }
            }
//        }
        return $status;
    }



    public function logout(): Redirect
    {
        $result = null;
        if(isset($_SESSION['userid'])){

            unset($_SESSION['username']);
            unset($_SESSION['userid']);
            session_destroy();

            $result = new Redirect('/');
        }

        return $result;
    }
}