<?php

namespace Controllers;

class TyroServ extends Controller
{

    protected $modelName = \Model\Users::class;
    protected $modelName2 = \Model\UsersTyroServ::class;

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
    public function connect()
    {

        // debug
        var_dump("POST ALL", $_POST);

        if(!empty($_POST['email_usertium']) && !empty($_POST['mdp_usertium']))
        {


            $email_auth = $_POST['email_usertium'];
            $mdp_auth = $_POST['mdp_usertium'];

            $userLoad = $this->model->findByEmail($email_auth);
            
            // debug
            var_dump("User LOAD",$userLoad);

            if (!empty($userLoad)){

                $mdpCrypt_auth = $this->model->chiffreMdp($mdp_auth);
                
                // debug
                var_dump("Mdp Auth", $mdpCrypt_auth);

                if($mdpCrypt_auth == $userLoad->password){
                    
                    $userTyroServLoad = $this->model2->findByIdUsers($userLoad->id);
                    
                    // debug
                    var_dump("User LOAD TyroServ", $userTyroServLoad);








                    if($userTyroServLoad == false){

                        header('Access-Control-Allow-Origin: *');
                        echo json_encode(["status"=>"true","why"=>"first connexion","task"=>"firstConnect"]);

                    }

                } else {

                    header('Access-Control-Allow-Origin: *');
                    echo json_encode(["status"=>"err","why"=>"bad password"]);

                }


            } else {

                header('Access-Control-Allow-Origin: *');
                echo json_encode(["status"=>"err","why"=>"non-existent account"]);

            }
            

        } else {

            header('Access-Control-Allow-Origin: *');
            echo json_encode(["status"=>"err","why"=>"indefinite fields"]);

        }
    }

    /**
     * 
     * First Connect TyroServ Launcher
     * @method : post
     * 
     */
    public function firstConnect()
    {
    
    
    }





}

