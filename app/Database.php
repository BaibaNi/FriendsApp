<?php
namespace App;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;

class Database
{
    private static $connection = null;

    /**
     * @throws Exception
     */
    public static function connection()
    {
        if(self::$connection === null){
            $connectionParams = [
                'dbname' => 'mini_fb',
                'user' => 'banibai',
                'password' => 'Learning_mysql_074',
                'host' => 'localhost',
                'driver' => 'pdo_mysql'
            ];
            self::$connection = DriverManager::getConnection($connectionParams);
        }
        return self::$connection;
    }
}