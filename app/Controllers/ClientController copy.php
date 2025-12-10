<?php
// app/Controllers/ClientController.php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../Models/Client.php';

class ClientController extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function requireLogin()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=loginForm');
            exit;
        }
    }

    private function getQuartiers(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, nomQuartier FROM quartier ORDER BY nomQuartier ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getEtats(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, libelEtat FROM etat ORDER BY libelEtat ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Liste des clients
     * URL : index.php?controller=client&action=index
     */
    public function index()
    {
        $this->requireLogin();

        $clients = Client::all();

        $this->render('client/index', [
            'title'   => 'Liste des clients',
            'clients' => $clients,
        ], 'dashboard');
    }

    /**
     * Créer un client
     * GET  : afficher le formulaire
     * POST : traiter le formulaire
     */
    public function create()
    {
        $this->requireLogin();

        $quartiers = $this->getQuartiers();
        $etats     = $this->getEtats();

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = [
                'nom'             => $_POST['nom']             ?? '',
                'postnom'         => $_POST['postnom']         ?? '',
                'prenom'          => $_POST['prenom']          ?? '',
                'sexe'            => $_POST['sexe']            ?? '',
                'adresse'         => $_POST['adresse']         ?? '',
                'idQuartier'      => $_POST['idQuartier']      ?? '',
                'telephone'       => $_POST['telephone']       ?? '',
                'personneContact' => $_POST['personneContact'] ?? '',
                'idEtat'          => $_POST['idEtat']          ?? '',
            ];

            // Nettoyage
            $data['idQuartier'] = $data['idQuartier'] === '' ? null : (int)$data['idQuartier'];
            $data['idEtat']     = $data['idEtat']     === '' ? null : (int)$data['idEtat'];

            // Validation simple
            if ($data['nom'] === '')       $errors[] = 'Le nom est obligatoire.';
            if ($data['postnom'] === '')   $errors[] = 'Le postnom est obligatoire.';
            if ($data['prenom'] === '')    $errors[] = 'Le prénom est obligatoire.';
            if ($data['sexe'] === '')      $errors[] = 'Le sexe est obligatoire.';
            if ($data['adresse'] === '')   $errors[] = 'L\'adresse est obligatoire.';
            if ($data['telephone'] === '') $errors[] = 'Le téléphone est obligatoire.';
            if ($data['idQuartier'] === null) $errors[] = 'Le quartier est obligatoire.';
            if ($data['idEtat'] === null)     $errors[] = 'L\'état du client est obligatoire.';

            // Vérifier que l'état existe
            if ($data['idEtat'] !== null) {
                $pdo = Database::getConnection();
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM etat WHERE id = :id");
                $stmt->execute(['id' => $data['idEtat']]);
                if ($stmt->fetchColumn() == 0) {
                    $errors[] = 'L\'état sélectionné est invalide.';
                }
            }

            if (!empty($errors)) {
                $this->render('client/form', [
                    'title'     => 'Ajouter un client',
                    'mode'      => 'create',
                    'errors'    => $errors,
                    'old'       => $data,
                    'quartiers' => $quartiers,
                    'etats'     => $etats,
                ], 'dashboard');
                return;
            }

            $client = new Client($data);
            $client->save();

            header('Location: index.php?controller=client&action=index');
            exit;
        }

        // GET : afficher formulaire
        $this->render('client/form', [
            'title'     => 'Ajouter un client',
            'mode'      => 'create',
            'quartiers' => $quartiers,
            'etats'     => $etats,
        ], 'dashboard');
    }

    /**
     * Éditer un client
     */
    public function edit()
    {
        $this->requireLogin();

        if (!isset($_GET['id'])) {
            die('ID client manquant.');
        }

        $id = (int)$_GET['id'];
        $client = Client::find($id);

        if (!$client) {
            die('Client introuvable.');
        }

        $quartiers = $this->getQuartiers();
        $etats     = $this->getEtats();
        $errors    = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = [
                'nom'             => $_POST['nom']             ?? $client->nom,
                'postnom'         => $_POST['postnom']         ?? $client->postnom,
                'prenom'          => $_POST['prenom']          ?? $client->prenom,
                'sexe'            => $_POST['sexe']            ?? $client->sexe,
                'adresse'         => $_POST['adresse']         ?? $client->adresse,
                'idQuartier'      => $_POST['idQuartier']      ?? $client->idQuartier,
                'telephone'       => $_POST['telephone']       ?? $client->telephone,
                'personneContact' => $_POST['personneContact'] ?? $client->personneContact,
                'idEtat'          => $_POST['idEtat']          ?? $client->idEtat,
            ];

            $data['idQuartier'] = $data['idQuartier'] === '' ? null : (int)$data['idQuartier'];
            $data['idEtat']     = $data['idEtat']     === '' ? null : (int)$data['idEtat'];

            if ($data['nom'] === '')       $errors[] = 'Le nom est obligatoire.';
            if ($data['postnom'] === '')   $errors[] = 'Le postnom est obligatoire.';
            if ($data['prenom'] === '')    $errors[] = 'Le prénom est obligatoire.';
            if ($data['sexe'] === '')      $errors[] = 'Le sexe est obligatoire.';
            if ($data['adresse'] === '')   $errors[] = 'L\'adresse est obligatoire.';
            if ($data['telephone'] === '') $errors[] = 'Le téléphone est obligatoire.';
            if ($data['idQuartier'] === null) $errors[] = 'Le quartier est obligatoire.';
            if ($data['idEtat'] === null)     $errors[] = 'L\'état du client est obligatoire.';

            if ($data['idEtat'] !== null) {
                $pdo = Database::getConnection();
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM etat WHERE id = :id");
                $stmt->execute(['id' => $data['idEtat']]);
                if ($stmt->fetchColumn() == 0) {
                    $errors[] = 'L\'état sélectionné est invalide.';
                }
            }

            if (!empty($errors)) {
                $this->render('client/form', [
                    'title'     => 'Modifier un client',
                    'mode'      => 'edit',
                    'client'    => $client,
                    'old'       => $data,
                    'errors'    => $errors,
                    'quartiers' => $quartiers,
                    'etats'     => $etats,
                ], 'dashboard');
                return;
            }

            // Mise à jour de l'objet
            $client->nom             = $data['nom'];
            $client->postnom         = $data['postnom'];
            $client->prenom          = $data['prenom'];
            $client->sexe            = $data['sexe'];
            $client->adresse         = $data['adresse'];
            $client->idQuartier      = $data['idQuartier'];
            $client->telephone       = $data['telephone'];
            $client->personneContact = $data['personneContact'];
            $client->idEtat          = $data['idEtat'];

            $client->save();

            header('Location: index.php?controller=client&action=index');
            exit;
        }

        // GET
        $this->render('client/form', [
            'title'     => 'Modifier un client',
            'mode'      => 'edit',
            'client'    => $client,
            'quartiers' => $quartiers,
            'etats'     => $etats,
        ], 'dashboard');
    }

    /**
     * Supprimer
     */
    public function delete()
    {
        $this->requireLogin();

        if (!isset($_GET['id'])) {
            die('ID client manquant.');
        }

        $id = (int)$_GET['id'];
        Client::delete($id);

        header('Location: index.php?controller=client&action=index');
        exit;
    }
}
