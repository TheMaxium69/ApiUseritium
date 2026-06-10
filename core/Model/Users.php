<?php

namespace Model;

use PDO;

class Users extends Model
{

    protected $table = "users";

    public $id;
    public $username;
    public $displayname;
    public $email;
    private $password;
    public $role;
    public $pp;
    private $webToken;



    /**
     * 
     * FindByEmail
     * 
     */
    function findByEmail(string $email)
    {
        $resultat =  $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $resultat->execute([
            "email"=> $email
        ]);

        $user = $resultat->fetchObject();

        return $user;
    }

    /**
     *
     * FindByUsername
     *
     */
    function findByUsername(string $username)
    {
        $resultat =  $this->pdo->prepare("SELECT * FROM {$this->table} WHERE username = :username");
        $resultat->execute([
            "username"=> $username
        ]);

        $user = $resultat->fetchObject();

        return $user;
    }


    /**
     * 
     * ChiffreMdp
     * 
     */
    function chiffreMdp($passwordAuth){

        $key = $this->salt;

        $passwordCrypt = md5($passwordAuth);
        $passwordCryptSalt = $passwordCrypt.md5($key);

        return $passwordCryptSalt;

    }

    /**
     * FindByWebToken
     */
    function findByWebToken(string $webToken)
    {
        $resultat = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE webtoken = :webToken");
        $resultat->execute(["webToken" => $webToken]);
        return $resultat->fetchObject();
    }

    /**
     * GenerateWebToken
     */
    function generateWebToken(int $id): string
    {
        $token = bin2hex(random_bytes(32));
        $requete = $this->pdo->prepare("UPDATE {$this->table} SET webtoken = :webToken WHERE id = :id");
        $requete->execute(["webToken" => $token, "id" => $id]);
        return $token;
    }

    /*
     *
     * Count User
     *
     * */
    function countUser()
    {
        $requestCount =  $this->pdo->prepare("SELECT COUNT(*) FROM {$this->table}");
        $requestCount->execute();
        $count = $requestCount->fetchColumn();
        return $count;

    }




}