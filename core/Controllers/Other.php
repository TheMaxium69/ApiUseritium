<?php

namespace Controllers;

class Other extends Controller
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

        $message = "Bienvenue dans l'API Useritium pour Autre";

        header('Access-Control-Allow-Origin: *');
        echo json_encode($message);
    }

    /**
     *
     * Connect Other API
     * @method : post
     *
     */
    public function connect()
    {

        if (!empty($_POST['email_useritium']) && !empty($_POST['mdp_useritium'])) {

            $email_auth = $_POST['email_useritium'];
            $mdp_auth = $_POST['mdp_useritium'];

            $userLoad = $this->default->findByEmail($email_auth);

            if (!empty($userLoad)) {

                $mdpCrypt_auth = $this->default->chiffreMdp($mdp_auth);

                if ($mdpCrypt_auth == $userLoad->password) {

                    // GOOD

                    $resultCompteUseritium = ["id" => $userLoad->id,
                        "email"=> $userLoad->email,
                        "username"=> $userLoad->username,
                        "displayName"=> $userLoad->displayname,
                        "pp"=>$userLoad->pp,
                    ];

                    header('Access-Control-Allow-Origin: *');
                    echo json_encode(["status"=>"true","why"=>"successfully connected","result"=>$resultCompteUseritium]);

                } else {

                    header('Access-Control-Allow-Origin: *');
                    echo json_encode(["status"=>"err","why"=>"bad passwd"]);


                }

            } else {

                header('Access-Control-Allow-Origin: *');
                echo json_encode(["status"=>"err","why"=>"bad email"]);

            }

        } else {

            header('Access-Control-Allow-Origin: *');
            echo json_encode(["status"=>"err","why"=>"wrong"]);

        }
    }

}