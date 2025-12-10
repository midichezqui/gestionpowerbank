<?php
// app/Controllers/AgentController.php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../Models/Agent.php';
require_once __DIR__ . '/../Models/Fonction.php';

class AgentController extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    private function requireLogin()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=loginForm');
            exit;
        }
    }

    private function loadFonctions()
    {
        return Fonction::all();
    }

    private function uploadPhoto(): ?string
    {
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $dir = __DIR__ . '/../../public/uploads/agents/';
        
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('agent_', true) . '.' . $ext;

        move_uploaded_file($_FILES['photo']['tmp_name'], $dir . $filename);

        return 'uploads/agents/' . $filename;
    }

    public function index()
    {
        $this->requireLogin();

        $agents = Agent::all();

        $this->render('agent/index', [
            'title'  => 'Liste des agents',
            'agents' => $agents
        ], 'dashboard');
    }

    public function create()
    {
        $this->requireLogin();

        // Seuls admin (2) et super (1) peuvent créer un agent
        $idFonction = $_SESSION['user_idFonction'] ?? null;
        if (!in_array((int)$idFonction, [1, 2], true)) {
            header('Location: index.php?controller=agent&action=index');
            exit;
        }

        $fonctions = $this->loadFonctions();
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = [
                'nom'        => $_POST['nom'] ?? '',
                'postnom'    => $_POST['postnom'] ?? '',
                'prenom'     => $_POST['prenom'] ?? '',
                'sexe'       => $_POST['sexe'] ?? '',
                'telephone'  => $_POST['telephone'] ?? '',
                'adresse'    => $_POST['adresse'] ?? '',
                'email'      => $_POST['email'] ?? '',
                'pseudo'     => $_POST['pseudo'] ?? '',
                'pwd'        => password_hash($_POST['pwd'], PASSWORD_DEFAULT),
                'idFonction' => $_POST['idFonction'] ?? null,
            ];

            $photo = $this->uploadPhoto();
            if ($photo) $data['photo'] = $photo;

            $agent = new Agent($data);
            $agent->save();

            header('Location: index.php?controller=agent&action=index');
            exit;
        }

        $this->render('agent/form', [
            'title'     => 'Ajouter un agent',
            'mode'      => 'create',
            'fonctions' => $fonctions
        ], 'dashboard');
    }

    public function edit()
    {
        $this->requireLogin();

        // Seuls admin (2) et super (1) peuvent modifier un agent
        $idFonction = $_SESSION['user_idFonction'] ?? null;
        if (!in_array((int)$idFonction, [1, 2], true)) {
            header('Location: index.php?controller=agent&action=index');
            exit;
        }

        if (!isset($_GET['id'])) die("ID manquant");
        $agent = Agent::find((int)$_GET['id']);
        if (!$agent) die("Agent introuvable");

        $fonctions = $this->loadFonctions();
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $agent->nom        = $_POST['nom'];
            $agent->postnom    = $_POST['postnom'];
            $agent->prenom     = $_POST['prenom'];
            $agent->sexe       = $_POST['sexe'];
            $agent->telephone  = $_POST['telephone'];
            $agent->adresse    = $_POST['adresse'];
            $agent->email      = $_POST['email'];
            $agent->pseudo     = $_POST['pseudo'];
            $agent->idFonction = $_POST['idFonction'];

            // Si un nouveau mot de passe est donné
            if (!empty($_POST['pwd'])) {
                $agent->pwd = password_hash($_POST['pwd'], PASSWORD_DEFAULT);
            }

            // Upload photo ?
            $photo = $this->uploadPhoto();
            if ($photo) {
                $agent->photo = $photo;
            }

            $agent->save();

            header('Location: index.php?controller=agent&action=index');
            exit;
        }

        $this->render('agent/form', [
            'title'     => 'Modifier un agent',
            'mode'      => 'edit',
            'agent'     => $agent,
            'fonctions' => $fonctions
        ], 'dashboard');
    }

    public function delete()
    {
        $this->requireLogin();

        // Seul le super (1) peut supprimer un agent
        $idFonction = $_SESSION['user_idFonction'] ?? null;
        if ((int)$idFonction !== 1) {
            header('Location: index.php?controller=agent&action=index');
            exit;
        }

        if (!isset($_GET['id'])) die("ID manquant");
        Agent::delete((int)$_GET['id']);

        header('Location: index.php?controller=agent&action=index');
        exit;
    }
}
