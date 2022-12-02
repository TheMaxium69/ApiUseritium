<?php

namespace Controllers;

class Home
{
    /**
     * 
     * Wiew index
     * @method : get
     * 
     */
    public function index()
    {

        $message = "Bienvenue dans l'API Useritium pour TyroServ";

        header('Access-Control-Allow-Origin: *');
        echo json_encode($message);
    }

    /**
     * 
     * Connect TyroServ Launcher
     * @method : post
     * 
     */
    public function connect(){

        if(!empty($_POST['email_usertium']) && !empty($_POST['mdp_usertium'])){

        

            $email_auth = $_POST['email_usertium'];
            $mdp_auth = $_POST['mdp_usertium'];
            
            







            




        } else {

            header('Access-Control-Allow-Origin: *');
            echo json_encode("Erreur");

        }



    }




}

