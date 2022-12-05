<?php

namespace Model;

//require_once "core/database.php";

abstract class Model
{
    protected $pdo;
    protected $salt;
    protected $table;

    public function __construct(){
         $this->pdo = \Database::getPdo();
         $this->salt = \DataBase::getSalt();
    }



/**
 * @param integer $id
 * @return array|bool
 */
public function find(int $id)
{

 

  $maRequete = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id =:id");

  $maRequete->execute(['id' => $id]);

  $item = $maRequete->fetch();

  return $item;

}
/**
 * @return array
 */
public function findAll() : array
{
       

        $resultat =  $this->pdo->query("SELECT * FROM {$this->table}");
        
        $items = $resultat->fetchAll();

        return $items;

}


/**
 * @param integer $id
 * @return void
 */
public function delete(int $id) :void
{
 

  $maRequete = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id =:id");

  $maRequete->execute(['id' => $id]);


} 

}