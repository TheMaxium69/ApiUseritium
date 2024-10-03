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
    private $skin;
    private $slim;


    /**
     * 
     * FindByIdUsers
     * 
     */
    function findByIdUsers($idUsers)
    {
        $resultat =  $this->pdo->prepare("SELECT * FROM {$this->table} WHERE idUsers = :idUsers");
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
        $resultat =  $this->pdo->prepare("SELECT * FROM {$this->table} WHERE pseudo = :pseudo");
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
        $requestUpdate = $this->pdo->prepare("UPDATE {$this->table} SET auth_nb = :newNbAuth , auth_date = :newDateJson WHERE idTyroServ = :id");

        $requestUpdate->execute([
            'id' => $id,
            'newNbAuth' => $newNbAuth,
            'newDateJson' => $newDateJson
        ]);

        $result = ["requeste" => "1", "newNbAuth" => $newNbAuth];


        return $result;


    }


    /**
     * 
     * CreateUser
     * 
     */
    function createUser($newUserTS)
    {
        $resultat =  $this->pdo->prepare("INSERT INTO {$this->table} (idUsers, pseudo, sanction, auth_nb, auth_date, token) VALUES (:idUsers, :pseudo, :sanction, :auth_nb, :auth_date, :token)");
        $resultat->execute([
            "idUsers" => $newUserTS[0],
            "pseudo"=> $newUserTS[1],
            "sanction"=> $newUserTS[2],
            "auth_nb"=> $newUserTS[3],
            "auth_date"=> $newUserTS[4],
            "token"=> $newUserTS[5] 
        ]);

        $userTyroServ = $resultat->fetchObject();

    }

    /*
     *
     * ChangeCape
     *
     * */
    function changeCape($idTyroServ, $idNewCape){

        $resultat = $this->pdo->prepare("UPDATE {$this->table} SET cape = :idNewCape WHERE idTyroServ = :idTyroServ");
        $resultat->execute([
            "idNewCape" => $idNewCape,
            "idTyroServ" => $idTyroServ
        ]);

    }

    /*
     *
     * ChangeSkin
     *
     * */
    function changeSkin($skinUrl, $idEdit){

        $resultat = $this->pdo->prepare("UPDATE {$this->table} SET skin = :skinUrl WHERE idUsers = :idEdit");
        $resultat->execute([
            "skinUrl" => $skinUrl,
            "idEdit" => $idEdit
        ]);

    }

    /*
     *
     * ResetSkin
     *
     * */
    function resetSkin($idEdit){

        $resultat = $this->pdo->prepare("UPDATE {$this->table} SET skin = null WHERE idUsers = :idEdit");
        $resultat->execute([
            "idEdit" => $idEdit
        ]);

    }




}   