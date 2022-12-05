<?php

namespace Model;

use PDO;

class TokenTyroServ extends Model
{

    protected $table = "token_tyroserv";

    public $id;
    public $date;
    public $tokenPublic;

    /**
     * 
     * publicTokenChiffre
     * 
     */
    function publicChiffre($tokenPrivate){

        $key = $this->salt;

        $tokenPrivateCrypt = md5($tokenPrivate);
        $tokenPrivateCryptSalt = $tokenPrivateCrypt.md5($key);

        $tokenPublic = $this->recupTokenPublic();
        $tokenPrivateCryptSalt_tokenPublic = $tokenPrivateCryptSalt.md5($tokenPublic[0]->tokenPublic);
        $tokenPrivateCryptSalt_tokenPublicOld = $tokenPrivateCryptSalt.md5($tokenPublic[1]->tokenPublic);


        return ["current" => $tokenPrivateCryptSalt_tokenPublic, "old" => $tokenPrivateCryptSalt_tokenPublicOld];

    }

    /**
     * 
     * recupTokenPublic
     * 
     */
    function recupTokenPublic()
    {
        $resultat =  $this->pdo->prepare("SELECT * FROM {$this->table} ORDER BY id DESC LIMIT 2;");
        $resultat->execute();

        $tokenPublic = $resultat->fetchAll();

        return $tokenPublic;
    }



}