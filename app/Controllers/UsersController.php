<?php

namespace App\Controllers;

use App\Database;

use App\Exceptions\RegistrationValidationException;
use App\Models\Friend;
use App\Redirect;
use App\Validation\Errors;
use App\Validation\RegistrationFormValidator;
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
        ]);
    }


    /** @throws Exception */
    public function show(array $vars): View
    {
        $usersQuery = Database::connection()
            ->prepare('SELECT * FROM users where id = ?');
        $usersQuery->bindValue(1, $vars['id']);
        $userList = $usersQuery->executeQuery()->fetchAssociative();


        $userProfileQuery = Database::connection()
            ->prepare('SELECT * FROM user_profiles where user_id = ?');
        $userProfileQuery->bindValue(1, $vars['id']);
        $userProfile = $userProfileQuery->executeQuery()->fetchAssociative();


        $user = new UserProfile($userProfile['name'], $userProfile['surname'], $userProfile['birthday'],
            $userList['email'], $userList['password'], $userList['created_at'], $userList['id']);



        $friendInvitesQuery = Database::connection()
            ->prepare('SELECT * FROM friend_invites where user_id = ? ');
        $friendInvitesQuery->bindValue(1, $_SESSION['userid']);
//        $friendInvitesQuery->bindValue(2, $_SESSION['userid']);
        $invitedByFriendIds = $friendInvitesQuery->executeQuery()->fetchAllAssociative();

        $invitedByFriends = [];
        foreach ($invitedByFriendIds as $friend) {
            $userProfileQuery = Database::connection()
                ->prepare('SELECT * FROM user_profiles where user_id = ?');
            $userProfileQuery->bindValue(1, $friend['friend_id']);
            $userProfile = $userProfileQuery->executeQuery()->fetchAssociative();

            $invitedByFriends[] = new Friend(
                $userProfile['name'],
                $userProfile['surname'],
                $friend['created_at'],
                $friend['id'], // invite id
                $friend['user_id'], // invited person
                $friend['friend_id'] // id of the inviter
            );
        }




        $friendAcceptsQuery = Database::connection()
            ->prepare('SELECT * FROM friends where user_id = ?');
        $friendAcceptsQuery->bindValue(1, $_SESSION['userid']);
//        $friendAcceptsQuery->bindValue(2, $_SESSION['userid']);
        $acceptedByFriendIds = $friendAcceptsQuery->executeQuery()->fetchAllAssociative();

        $acceptedByFriends = [];
        foreach ($acceptedByFriendIds as $friendAccept){
            $userProfileQuery = Database::connection()
                ->prepare('SELECT * FROM user_profiles where user_id = ?');
            $userProfileQuery->bindValue(1, $friendAccept['friend_id']);
            $userProfile = $userProfileQuery->executeQuery()->fetchAssociative();

            $acceptedByFriends[] = new Friend(
                $userProfile['name'],
                $userProfile['surname'],
                $friendAccept['accepted_at'],
                $friendAccept['id'], // invite id
                $friendAccept['user_id'], // invited person
                $friendAccept['friend_id'] // id of the inviter
            );
        }


        return new View('Users/show', [
            'user' => $user,
            'invitedByFriends' => $invitedByFriends,
            'acceptedByFriends' => $acceptedByFriends
        ]);
    }


//---USER REGISTRATION---
    public function getRegister(): View
    {
        return new View('Users/register', [
            'errors' => Errors::getAll(),
            'inputs' => $_SESSION['inputs'] ?? []
        ]);
    }

    /** @throws Exception */
    public function register(): Redirect
    {
        $validator = new RegistrationFormValidator($_POST, [
            'name' => ['required', 'Min:3'],
            'surname' => ['required', 'Min:3'],
            'birthday' => ['required'],
            'email' => ['required', 'Min:3'],
            'password' => ['required', 'Min:3']
        ]);
        try {
            $validator->passes();

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

        } catch(RegistrationValidationException $exception){
            $_SESSION['errors'] = $validator->getErrors();
            $_SESSION['inputs'] = $_POST;
            return new Redirect("/users/register");
        }

    }


    public function invite(array $vars): Redirect
    {
        if($vars['id'] !== $_SESSION['userid']){

            $friendsAcceptsQuery = Database::connection()
                ->prepare('SELECT * from friends where user_id = ? and friend_id = ?');
            $friendsAcceptsQuery->bindValue(1, $_SESSION['userid']);
            $friendsAcceptsQuery->bindValue(2, $vars['id']);
            $friendAccepts = $friendsAcceptsQuery->executeQuery()->fetchAllAssociative();


            $friendsInvitesQuery = Database::connection()
                ->prepare('SELECT * from friend_invites where user_id = ? and friend_id = ?');
            $friendsInvitesQuery->bindValue(1, $vars['id']);
            $friendsInvitesQuery->bindValue(2, $_SESSION['userid']);
            $friendInvites = $friendsInvitesQuery->executeQuery()->fetchAllAssociative();


//            var_dump($friendAccepts);
//            var_dump($friendInvites);die;

            if(empty($friendAccepts) && empty($friendInvites)) {
                Database::connection()
                    ->insert('friend_invites', [
                        'user_id' => (int)$vars['id'],
                        'friend_id' => $_SESSION['userid'],
                    ]);
            } else {
                //todo
                var_dump('You are already friends.');
            }
        } else{
            //todo
            var_dump('You cannot invite yourself');
        }
        return new Redirect("/users/{$vars['id']}");
    }


    public function accept(array $vars): Redirect
    {
        if($vars['id'] === $_SESSION['userid']) {

            $friendsQuery = Database::connection()
                ->prepare('SELECT * FROM friend_invites where user_id = ?'); // and user_id not in (SELECT friend_id from friends where friend_id = ?)
            $friendsQuery->bindValue(1, $_SESSION['userid']);
//            $friendsQuery->bindValue(2, $_SESSION['userid']);
            $friendInvites = $friendsQuery->executeQuery()->fetchAllAssociative();

//            var_dump($friendInvites); die;

            foreach ($friendInvites as $invite){
                if($invite['id'] === $vars['inviteid']){

                    Database::connection()
                        ->insert('friends', [
                            'user_id' => (int) $invite['user_id'],
                            'friend_id' => (int) $invite['friend_id'],
                        ]);

                    Database::connection()
                        ->insert('friends', [
                            'user_id' => (int) $invite['friend_id'],
                            'friend_id' => (int) $invite['user_id'],
                        ]);

                    Database::connection()
                        ->delete('friend_invites', [
                            'id' => $invite['id']
                        ]);

                    return new Redirect('/users/' . $_SESSION['userid']);
                }
            }

//            if (count($friendInvites) > 0) {
//                //todo Errors
//                var_dump('You have already accepted');
//            } else {
//            }

        } else{
            //todo
            var_dump('You cannot invite yourself');
        }
        return new Redirect('/users/' . $_SESSION['userid']);
    }

}
