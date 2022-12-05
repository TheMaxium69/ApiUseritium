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

        if(!empty($_POST['email_useritium']) && !empty($_POST['mdp_useritium']))
        {

            $email_auth = $_POST['email_useritium'];
            $mdp_auth = $_POST['mdp_useritium'];

            $userLoad = $this->model->findByEmail($email_auth);       

            if (!empty($userLoad)){

                $mdpCrypt_auth = $this->model->chiffreMdp($mdp_auth);

                if($mdpCrypt_auth == $userLoad->password){
                    
                    $userTyroServLoad = $this->model2->findByIdUsers($userLoad->id);

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

    /**
     * 
     * Verification Serveur
     * @method : post
     * 
     */
    public function servVerif()
    {

        if(!empty($_POST['pseudo']) && !empty($_POST['token'])  && !empty($_POST['tokenTwo']))
        {
            $isNbVerif = false;
            $isTokenVerif = false;

            $pseudo = $_POST['pseudo'];
            $auth_tokenChiffre = $_POST['token'];
            $auth_nbChiffre = $_POST['tokenTwo'];

            $userTyroServLoad = $this->model2->findByPseudo($pseudo);
            var_dump($userTyroServLoad);

            $nbChiffre = /*$this->model3->publicChiffre($resultNewConnexion["newNbAuth"]);*/ $userTyroServLoad->auth_nb;
            $tokenChiffre = /*$this->model3->publicChiffre($userTyroServLoad->token);*/ $userTyroServLoad->token;
            var_dump($nbChiffre, $tokenChiffre);

            if($auth_nbChiffre == $nbChiffre){ $isNbVerif = true; }

            if($auth_tokenChiffre == $tokenChiffre){ $isTokenVerif = true; }

            if($isTokenVerif == true && $isNbVerif == true){ $reponse = ["status"=>"true","auth_pseudo"=>$pseudo]; }
            else {

                if($isNbVerif == true) {

                    $reponse = ["status"=>"err",
                                "why"=>[
                                    "Token"=> "false",
                                    "AuthNb" => "true"
                                ]
                            ];

                } else if($isTokenVerif == true) {

                    $reponse = ["status"=>"err",
                                "why"=>[
                                    "Token"=> "true",
                                    "AuthNb" => "false"
                                ]
                            ];

                } else {

                    $reponse = ["status"=>"err",
                                "why"=>[
                                    "Token"=> "false",
                                    "AuthNb" => "false"
                                ]
                            ];

                } 

            }
            
            header('Access-Control-Allow-Origin: *');
            echo json_encode($reponse);

        } else {

            header('Access-Control-Allow-Origin: *');
            echo json_encode(["status"=>"err","why"=>"indefinite fields"]);

        }

    
    
    }




}

