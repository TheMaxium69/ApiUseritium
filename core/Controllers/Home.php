<?php

namespace Controllers;

class Home extends Controller
{

    /**
     * 
     * Wiew index
     * @method : get
     * 
     */
    public function index()
    {

        $message = "Bienvenue dans l'API Useritium des projets Externe";

        header('Access-Control-Allow-Origin: *');
        echo json_encode($message);
    }




}

