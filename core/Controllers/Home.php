<?php

namespace Controllers;

class Home extends Controller
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

        $message = "Bienvenue dans l'API Useritium des projets Externe";

        header('Access-Control-Allow-Origin: *');
        echo json_encode($message);
    }




}

