<?php
namespace App\Controllers;

use App\Database;
use App\Models\UserProfile;
use App\Redirect;
use App\View;
use Doctrine\DBAL\Exception;

class AccessController extends Database
{
//---LOGIN---

    /**  @throws Exception */
    public function getWelcome(array $vars): View
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
            $userList['email'], $userList['password'], $userList['created_at'], $userList['id']
        );

        return new View('Users/welcome', [
            'user' => $user
        ]);

    }

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

                    $status = new Redirect('/users/welcome/' . $user[0]['id']);

                    session_start();
                    $_SESSION['userid'] = $user[0]["id"];

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
    var_dump('test');
        $result = null;
        if(isset($_SESSION['userid'])){

            session_destroy();
            unset($_SESSION['userid']);

            $result = new Redirect('/');
        }

        return $result;
    }
}