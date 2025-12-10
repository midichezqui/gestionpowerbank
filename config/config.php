<?php
/**
 * Configuration principale de l'application
 * -----------------------------------------
 * Ce fichier contient :
 *  - Les paramètres de connexion MySQL
 *  - Les constantes globales de l'application
 *  - L'environnement (dev / prod)
 */

// MODE DE L'APPLICATION : 'dev' ou 'prod'
define('APP_ENV', 'dev');

// INFORMATIONS DE CONNEXION À LA BASE DE DONNÉES
// On utilise APP_ENV pour distinguer la config locale (dev) et en ligne (prod)
if (APP_ENV === 'dev') {
    // Configuration locale
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'db_location');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    // Configuration en ligne
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'powerstate_bank');
    define('DB_USER', 'powerstate_chrono');
    define('DB_PASS', 'Php123java');
}

// FUSEAU HORAIRE GLOBAL
date_default_timezone_set('Africa/Kinshasa');

// URL DE BASE DU PROJET (si besoin)
define('BASE_URL', 'http://localhost/powerbank_app/');

/**
 * Mode debug
 * Affiche les erreurs uniquement en mode développement
 */
if (APP_ENV === 'dev') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
