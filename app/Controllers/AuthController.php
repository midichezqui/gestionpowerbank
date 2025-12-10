<?php
// app/Controllers/AuthController.php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../Models/User.php';

class AuthController extends Controller
{
    public function __construct()
    {
        // S'assurer que la session est démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Afficher le formulaire de connexion
     * URL : index.php?controller=auth&action=loginForm
     */
    public function loginForm()
    {
        // Si déjà connecté, on renvoie vers le dashboard (HomeController@index)
        if (!empty($_SESSION['user_id'])) {
            header('Location: index.php?controller=dashboard&action=index');
            
            exit;
        }

        $this->render('auth/login', [
            'title' => 'Connexion agent',
        ]);
    }

    /**
     * Traiter le formulaire de connexion (POST)
     * URL : index.php?controller=auth&action=login
     */
    public function login()
    {
        // On n’accepte que le POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=auth&action=loginForm');
            exit;
        }

        $pseudo   = $_POST['pseudo']   ?? '';
        $password = $_POST['password'] ?? '';

        // Vérification basique des champs
        if (empty($pseudo) || empty($password)) {
            $this->render('auth/login', [
                'error'      => 'Veuillez saisir le pseudo et le mot de passe.',
                'old_pseudo' => $pseudo,
            ]);
            return;
        }

        // Vérification via le modèle User (table agent)
        $user = User::verifyCredentials($pseudo, $password);

        if (!$user) {
            // Mauvais identifiants
            $this->render('auth/login', [
                'error'      => 'Pseudo ou mot de passe incorrect.',
                'old_pseudo' => $pseudo,
            ]);
            return;
        }

        // Connexion réussie : on enregistre les infos utiles en session
        $_SESSION['user_id']        = $user->id;
        $_SESSION['user_pseudo']    = $user->pseudo;
        $_SESSION['user_nom']       = $user->nom;
        $_SESSION['user_postnom']   = $user->postnom;
        $_SESSION['user_prenom']    = $user->prenom;
        $_SESSION['user_email']     = $user->email;
        $_SESSION['user_idFonction']= $user->idFonction;

        // Redirection vers le dashboard (à adapter selon ton contrôleur de base)
        //header('Location: index.php?controller=home&action=index');
        header('Location: index.php?controller=dashboard&action=index');
        exit;
    }

    /**
     * Déconnexion de l’agent
     * URL : index.php?controller=auth&action=logout
     */
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // On vide les données de session
        $_SESSION = [];
        session_destroy();

        // Retour à la page de connexion
        header('Location: index.php?controller=auth&action=loginForm');
        exit;
    }
}
