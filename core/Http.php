<?php

class Http
{

/**
 * @param string $url
 */

public static function redirect(string $url) : void 

{

    header('Location: '.$url);
    
}

}