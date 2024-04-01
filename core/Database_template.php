<?php
class Database
{

    public static function getPdo(){

        $pdo = new PDO('mysql:host=127.0.0.1;dbname=useritium','root' ,'', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_PERSISTENT
        ]);

        return $pdo;

    }

    public static function getSalt(){

        require "Salt.php";

        return $salt;

    }
}



