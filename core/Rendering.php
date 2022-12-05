<?php


class Rendering

{


        /**
         * 
         * @param string $template
         * @param array $donnees
         * 
         */
        public static function render(string $template, array $donnees):void
        {


            extract($donnees);
        
            ob_start();


            require_once "templates/".$template.".html.php";
        
            
            $contenuDeLaPage = ob_get_clean();
            
            
            require_once "templates/layout.html.php";

        }


}