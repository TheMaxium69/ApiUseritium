<?php

namespace Controllers;

abstract class Controller
{

     protected $model;
     protected $model2;

     protected $modelName;
     protected $modelName2;

        public function __construct(){

            $this->model = new $this->modelName();
            $this->model2 = new $this->modelName2();
        }



}