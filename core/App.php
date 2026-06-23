<?php

class App
{
    
    private static $routes = [
        'Home'      => ['index'],
        'WebSite'   => ['index', 'connect', 'connectToken'],
        'TyroServ'  => ['index', /*'connect', 'connectToken', 'servVerif', 'connectPanelAdmin', 'getSkinByPseudo', 'getCapeByPseudo', 'player', 'changeSkin', 'changeCape', 'countPlayer'*/],
        'Gamenium'  => ['index', 'connect'],
        'Other'     => ['index', 'connect'],
        'Oauth'     => ['authorize', 'token', 'userinfo'],
    ];

    public static function process()
    {
        if (empty($_GET['controller'])) {
            header('Location: https://useritium.fr/403', true, 302);
            exit;
        }

        $controllerName = ucfirst($_GET['controller']);
        $task = !empty($_GET['task']) ? $_GET['task'] : 'index';

        if (!isset(self::$routes[$controllerName]) || !in_array($task, self::$routes[$controllerName], true)) {
            header('Location: https://useritium.fr/404', true, 302);
            exit;
        }

        $controllerClass = "\Controllers\\" . $controllerName;
        $controller = new $controllerClass();
        $controller->$task();
    }
}