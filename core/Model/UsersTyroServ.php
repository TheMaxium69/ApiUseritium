<?php

namespace Model;

use PDO;

class UsersTyroServ extends Model
{

    protected $table = "users_tyroserv";

    public $idTyroServ;
    public $idUsers;
    public $pseudo;
    public $sanction;
    public $auth_nb;
    public $auth_date;


    /**
     * 
     * FindByIdUsers
     * 
     */
    function findByIdUsers($idUsers)
    {
        $resultat =  $this->pdo->prepare('SELECT * FROM users_tyroserv WHERE idUsers = :idUsers');
        $resultat->execute([
            "idUsers"=> $idUsers
        ]);

        $userTyroServ = $resultat->fetchObject();

        return $userTyroServ;
    }





}   