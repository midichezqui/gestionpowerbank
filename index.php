<?php
require_once __DIR__ . '/core/Router.php';

session_start();

// Si aucun contrôleur n'est passé dans l'URL
if (!isset($_GET['controller'])) {
    if (!empty($_SESSION['user_id'])) {
        // Déjà connecté → dashboard
        $_GET['controller'] = 'home';
        $_GET['action']     = 'index';
    } else {
        // Non connecté → page de login
        $_GET['controller'] = 'auth';
        $_GET['action']     = 'loginForm';
    }
} else {
    if (!isset($_GET['action'])) {
        $_GET['action'] = 'index';
    }
}

$router = new Router();
$router->dispatch($_GET);
