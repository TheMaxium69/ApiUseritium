<?php

namespace Controllers;

class WebSite extends Controller
{
    protected $modelDefault = \Model\Users::class;
    protected $modelTyroServUser = \Model\UsersTyroServ::class;
    protected $modelTyroServToken = \Model\TokenTyroServ::class;

    /**
     * @method : get
     */
    public function index()
    {
        header('Access-Control-Allow-Origin: *');
        echo json_encode("Bienvenue dans l'API Useritium pour les sites de Tyrolium");
    }

    /**
     * @method : post
     */
    public function connect()
    {
        if (!empty($_POST['login_useritium']) && !empty($_POST['mdp_useritium'])) {

            $login_auth = $_POST['login_useritium'];
            $mdp_auth   = $_POST['mdp_useritium'];

            if (filter_var($login_auth, FILTER_VALIDATE_EMAIL)) {
                $userLoad = $this->default->findByEmail($login_auth);
            } else {
                $userLoad = $this->default->findByUsername($login_auth);
            }

            if (!empty($userLoad)) {

                $mdpCrypt_auth = $this->default->chiffreMdp($mdp_auth);

                if ($mdpCrypt_auth == $userLoad->password) {

                    $webToken = !empty($userLoad->webtoken)
                        ? $userLoad->webtoken
                        : $this->default->generateWebToken($userLoad->id);

                    $result = [
                        "id"          => $userLoad->id,
                        "email"       => $userLoad->email,
                        "username"    => $userLoad->username,
                        "displayname" => $userLoad->displayname,
                        "pp"          => $userLoad->pp,
                        "webToken"    => $webToken,
                    ];

                    header('Access-Control-Allow-Origin: *');
                    echo json_encode(["status" => "true", "why" => "successfully connected", "result" => $result]);

                } else {

                    header('Access-Control-Allow-Origin: *');
                    echo json_encode(["status" => "err", "why" => "bad password"]);

                }

            } else {

                header('Access-Control-Allow-Origin: *');
                echo json_encode(["status" => "err", "why" => "non-existent account"]);

            }

        } else {

            header('Access-Control-Allow-Origin: *');
            echo json_encode(["status" => "err", "why" => "indefinite fields", "require" => "post:login_useritium,mdp_useritium"]);

        }
    }

    /**
     * @method : post
     */
    public function connectToken()
    {
        if (!empty($_POST['webtoken_useritium'])) {

            $userLoad = $this->default->findByWebToken($_POST['webtoken_useritium']);

            if (!empty($userLoad)) {

                $result = [
                    "id"          => $userLoad->id,
                    "email"       => $userLoad->email,
                    "username"    => $userLoad->username,
                    "displayname" => $userLoad->displayname,
                    "pp"          => $userLoad->pp,
                    "webToken"    => $_POST['webtoken_useritium'],
                ];

                header('Access-Control-Allow-Origin: *');
                echo json_encode(["status" => "true", "why" => "successfully connected", "result" => $result]);

            } else {

                header('Access-Control-Allow-Origin: *');
                echo json_encode(["status" => "err", "why" => "invalid token"]);

            }

        } else {

            header('Access-Control-Allow-Origin: *');
            echo json_encode(["status" => "err", "why" => "indefinite fields", "require" => "post:webtoken_useritium"]);

        }
    }
}
