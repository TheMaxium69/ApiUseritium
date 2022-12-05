<?php

namespace Controllers;

abstract class Controller
{

     protected $default;
     protected $ts_user;
     protected $ts_token;

     protected $modelDefault;
     protected $modelTyroServUser;
     protected $modelTyroServToken;

        public function __construct(){

            $this->default = new $this->modelDefault();
            $this->ts_user = new $this->modelTyroServUser();
            $this->ts_token = new $this->modelTyroServToken();

        }



}