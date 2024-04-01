<?php

namespace Controllers;

class TyroServ extends Controller
{

    protected $modelDefault = \Model\Users::class;
    protected $modelTyroServUser = \Model\UsersTyroServ::class;
    protected $modelTyroServToken = \Model\TokenTyroServ::class;

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

            $userLoad = $this->default->findByEmail($email_auth);       

            if (!empty($userLoad)){

                $mdpCrypt_auth = $this->default->chiffreMdp($mdp_auth);

                if($mdpCrypt_auth == $userLoad->password){
                    
                    $userTyroServLoad = $this->ts_user->findByIdUsers($userLoad->id);

                    if($userTyroServLoad){

                        date_default_timezone_set('Europe/Paris');
                        $dateAuth = date('d-m-y h:i:s A');
                        
                        $resultNewConnexion = $this->ts_user->newConnexion($dateAuth, $userTyroServLoad->auth_nb, $userTyroServLoad->auth_date, $userTyroServLoad->idTyroServ);
                        
                        $nbAuthChiffre = $this->ts_token->publicChiffre($resultNewConnexion["newNbAuth"]);
                        $tokenChiffre = $this->ts_token->publicChiffre($userTyroServLoad->token);

                        $resultLauncher = ["pseudo" => $userTyroServLoad->pseudo, 
                                           "sanction"=> $userTyroServLoad->sanction, 
                                           "token"=> $tokenChiffre['current'],
                                           "tokenTwo"=> $nbAuthChiffre['current'],
                                           "skin"=>$userTyroServLoad->skin,
                                           "useritium"=>[
                                               "pp"=>$userLoad->pp,
                                               "username"=>$userLoad->username,
                                               "displayname"=>$userLoad->displayname,
                        ]];
                        
                        header('Access-Control-Allow-Origin: *');
                        echo json_encode(["status"=>"true","why"=>"successfully connected","result"=>$resultLauncher]);

                    } else {

                        // First Connexion
                        if(!empty($_POST['pseudo_tyroserv'])){


                            $idUsers = $userLoad->id;
                            $pseudo = $_POST['pseudo_tyroserv'];
                            $sanction = "{}";
                            $auth_nb = 0;
                            $auth_date = "{}";
                            $token = base_convert(hash('sha256', time() . mt_rand()), 16, 36);

                            if($this->ts_user->findByPseudo($pseudo)){

                                header('Access-Control-Allow-Origin: *');
                                echo json_encode(["status"=>"err","why"=>"pseudo exists"]);

                            } else {

                                $newUserTS = [$idUsers, $pseudo, $sanction, $auth_nb, $auth_date, $token];
                                $this->ts_user->createUser($newUserTS);
  
                                $newUserTyroServLoad = $this->ts_user->findByIdUsers($idUsers);
                                
                                if($newUserTyroServLoad){

                                    date_default_timezone_set('Europe/Paris');
                                    $dateAuth = date('d-m-y h:i:s A');
                                    
                                    $resultNewConnexion = $this->ts_user->newConnexion($dateAuth, $newUserTyroServLoad->auth_nb, $newUserTyroServLoad->auth_date, $newUserTyroServLoad->idTyroServ);
                                    
                                    $nbAuthChiffre = $this->ts_token->publicChiffre($resultNewConnexion["newNbAuth"]);
                                    $tokenChiffre = $this->ts_token->publicChiffre($newUserTyroServLoad->token);
            
                                    $resultLauncher = ["pseudo" => $newUserTyroServLoad->pseudo, 
                                                       "sanction"=> $newUserTyroServLoad->sanction, 
                                                       "token"=> $tokenChiffre['current'],
                                                       "tokenTwo"=> $nbAuthChiffre['current'],];
                                    
                                    header('Access-Control-Allow-Origin: *');
                                    echo json_encode(["status"=>"true","why"=>"account created successfully","result"=>$resultLauncher]);
            
                                } else {

                                    
                                    header('Access-Control-Allow-Origin: *');
                                    echo json_encode(["status"=>"err","why"=>"bdd erreur"]);


                                }


                            }

                        } else {
                            
                            header('Access-Control-Allow-Origin: *');
                            echo json_encode(["status"=>"true","why"=>"first connexion","require"=>"post:pseudo_tyroserv","keep"=> $userLoad->username]);

                        }


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
     * Connect Token TyroServ Launcher
     * @method : post
     *
     */
    public function connectToken()
    {

        if(!empty($_POST['token_useritium']) && !empty($_POST['email_useritium']))
        {
            
            $token_auth = $_POST['token_useritium'];
            $email_auth = $_POST['email_useritium'];

            $userLoad = $this->default->findByEmail($email_auth);

            if (!empty($userLoad)){

                $userTyroServLoad = $this->ts_user->findByIdUsers($userLoad->id);

                    if($userTyroServLoad){

                        $tokenChiffre = $this->ts_token->publicChiffre($userTyroServLoad->token);

                        if ($tokenChiffre['current'] !== $token_auth) {

                            header('Access-Control-Allow-Origin: *');
                            echo json_encode(["status"=>"err","why"=>"bad token"]);

                            exit();

                        }

                        date_default_timezone_set('Europe/Paris');
                        $dateAuth = date('d-m-y h:i:s A');

                        $resultNewConnexion = $this->ts_user->newConnexion($dateAuth, $userTyroServLoad->auth_nb, $userTyroServLoad->auth_date, $userTyroServLoad->idTyroServ);

                        $nbAuthChiffre = $this->ts_token->publicChiffre($resultNewConnexion["newNbAuth"]);
                        $tokenChiffre = $this->ts_token->publicChiffre($userTyroServLoad->token);

                        $resultLauncher = ["pseudo" => $userTyroServLoad->pseudo,
                                           "sanction"=> $userTyroServLoad->sanction,
                                           "token"=> $tokenChiffre['current'],
                                           "tokenTwo"=> $nbAuthChiffre['current'],
                                           "skin"=>$userTyroServLoad->skin,
                                           "useritium"=>[
                                               "pp"=>$userLoad->pp,
                                               "username"=>$userLoad->username,
                                               "displayname"=>$userLoad->displayname,
                        ]];

                        header('Access-Control-Allow-Origin: *');
                        echo json_encode(["status"=>"true","why"=>"successfully connected","result"=>$resultLauncher]);

                    } else {

                        header('Access-Control-Allow-Origin: *');
                        echo json_encode(["status"=>"err","why"=>"bdd erreur"]);

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

            $userTyroServLoad = $this->ts_user->findByPseudo($pseudo);

            $nbChiffre = $this->ts_token->publicChiffre($userTyroServLoad->auth_nb);;
            $tokenChiffre = $this->ts_token->publicChiffre($userTyroServLoad->token);

            if($auth_nbChiffre == $nbChiffre['current'] || $auth_nbChiffre == $nbChiffre['old']){ $isNbVerif = true; }

            if($auth_tokenChiffre == $tokenChiffre['current'] || $auth_tokenChiffre == $tokenChiffre['old']){ $isTokenVerif = true; }

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

