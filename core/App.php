<?php

class App
{
    
    private static $routes = [
        'Home'      => ['index'],
        'WebSite'   => ['index', 'connect', 'connectToken'],
        'TyroServ'  => ['index', /*'connect', 'connectToken', 'servVerif', 'connectPanelAdmin', 'getSkinByPseudo', 'getCapeByPseudo', 'player', 'changeSkin', 'changeCape', 'countPlayer'*/],
        'Gamenium'  => ['index', 'connect'],
        'Other'     => ['index', 'connect'],
    ];

    public static function process()
    {
        $controllerName = 'Home';
        $task = 'index';

        if (!empty($_GET['controller'])) {
            $controllerName = ucfirst($_GET['controller']);
        }
        if (!empty($_GET['task'])) {
            $task = $_GET['task'];
        }

        if (!isset(self::$routes[$controllerName]) || !in_array($task, self::$routes[$controllerName], true)) {
            http_response_code(404);
            header('Access-Control-Allow-Origin: *');
            echo json_encode(["status" => "err", "why" => "not found"]);
            return;
        }

        $controllerClass = "\Controllers\\" . $controllerName;
        $controller = new $controllerClass();
        $controller->$task();
    }
}