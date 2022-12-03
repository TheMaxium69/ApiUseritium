<?php

namespace Controllers;

class Home extends Controller
{

    protected $modelName = \Model\Users::class;

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

        if(!empty($_POST['email_usertium']) && !empty($_POST['mdp_usertium']))
        {

            var_dump("POST ALL", $_POST);

            $email_auth = $_POST['email_usertium'];
            $mdp_auth = $_POST['mdp_usertium'];

            $userLoad = $this->model->findByEmail($email_auth);
            
            var_dump("User LOAD",$userLoad);

            $mdpCrypt_auth = $this->model->chiffreMdp($mdp_auth);


            var_dump($mdpCrypt_auth);



            

        } else {

            header('Access-Control-Allow-Origin: *');
            echo json_encode("Erreur");

        }
    }




}

