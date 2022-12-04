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

                    if($userTyroServLoad){

                        date_default_timezone_set('Europe/Paris');
                        $dateAuth = date('d-m-y h:i:s A');
                        
                        $resultNewConnexion = $this->model2->newConnexion($dateAuth, $userTyroServLoad->auth_nb, $userTyroServLoad->auth_date, $userTyroServLoad->idTyroServ);
                        
                        $nbAuthChiffre = /*$this->model3->publicChiffre($resultNewConnexion["newNbAuth"]);*/ $resultNewConnexion["newNbAuth"];
                        $tokenChiffre = /*$this->model3->publicChiffre($userTyroServLoad->token);*/ $userTyroServLoad->token;

                        $resultLauncher = ["pseudo" => $userTyroServLoad->pseudo, 
                                           "sanction"=> $userTyroServLoad->sanction, 
                                           "token"=> $tokenChiffre,
                                           "tokenTwo"=> $nbAuthChiffre,];
                        
                        header('Access-Control-Allow-Origin: *');
                        echo json_encode(["status"=>"true","why"=>"first connexion","task"=>"firstConnect","result"=>$resultLauncher]);

                    } else {

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

