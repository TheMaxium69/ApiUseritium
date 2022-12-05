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
    private $token;


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

    /**
     * 
     * FindByPseudo
     * 
     */
    function findByPseudo($pseudo)
    {
        $resultat =  $this->pdo->prepare('SELECT * FROM users_tyroserv WHERE pseudo = :pseudo');
        $resultat->execute([
            "pseudo"=> $pseudo
        ]);

        $userTyroServ = $resultat->fetchObject();

        return $userTyroServ;
    }

    /**
     * 
     * NewConnexion
     * 
     */
    function newConnexion($date, $oldNbAuth, $oldDateJson, $id)
    {

        if($oldDateJson != false && $oldDateJson != "{}"){

            $oldDate = json_decode($oldDateJson, true);

            $countDateJson = count($oldDate);

            $oldDate[$countDateJson + 1] = $date;
         
        } else {

            $oldDate = ["1" => $date];

        }

        // JSON DATE
        $newDateJson = json_encode($oldDate);
        // NB AUTH
        $newNbAuth = $oldNbAuth + 1;


        // insert bdd
        $requestUpdate = $this->pdo->prepare("UPDATE users_tyroserv SET auth_nb = :newNbAuth , auth_date = :newDateJson WHERE idTyroServ = :id");

        $requestUpdate->execute([
            'id' => $id,
            'newNbAuth' => $newNbAuth,
            'newDateJson' => $newDateJson
        ]);

        $result = ["requeste" => "1", "newNbAuth" => $newNbAuth];


        return $result;


    }





}   