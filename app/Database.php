<?php
use Doctrine\DBAL\DriverManager;

class Database
{
    public function connect()
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