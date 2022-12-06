<?php

namespace Controllers;

class TyroPanel extends Controller
{

    /**
     * 
     * Wiew index
     * @method : get
     * 
     */
    public function index()
    {

        $message = "Bienvenue dans l'API Useritium pour TyroPanel administrateur";

        header('Access-Control-Allow-Origin: *');
        echo json_encode($message);
    }




}

