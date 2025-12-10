<?php
// core/Router.php

class Router
{
    /**
     * Dispatch vers le bon contrôleur / action
     *
     * @param array $params Généralement $_GET avec 'controller' et 'action'
     */
    public function dispatch(array $params = [])
    {
        // Nom du contrôleur (ex: 'auth', 'home', 'location' ...)
        $controllerName = isset($params['controller']) ? $params['controller'] : 'auth';
        $actionName     = isset($params['action']) ? $params['action'] : 'loginForm';

        // On construit le nom de la classe : 'AuthController', 'HomeController', etc.
        $controllerClass = ucfirst($controllerName) . 'Controller';

        // Chemin vers le fichier du contrôleur
        $controllerFile = __DIR__ . '/../app/Controllers/' . $controllerClass . '.php';

        if (!file_exists($controllerFile)) {
            // Contrôleur introuvable
            http_response_code(404);
            die("Contrôleur introuvable : {$controllerClass}");
        }

        // On charge la classe du contrôleur
        require_once $controllerFile;

        if (!class_exists($controllerClass)) {
            http_response_code(500);
            die("Classe contrôleur {$controllerClass} non définie.");
        }

        // On instancie le contrôleur (il étend normalement la classe Controller)
        $controller = new $controllerClass();

        // Vérifier que l'action existe
        if (!method_exists($controller, $actionName)) {
            http_response_code(404);
            die("Action '{$actionName}' introuvable dans le contrôleur {$controllerClass}.");
        }

      
        // Appel de l'action
        $controller->$actionName();
    }
}
