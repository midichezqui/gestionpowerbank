<?php
// app/Controllers/PowerbankController.php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../Models/Powerbank.php';

class PowerbankController extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Vérifier que l'utilisateur est connecté
     */
    private function requireLogin()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=loginForm');
            exit;
        }
    }

    /**
     * Récupérer la liste des types de câble
     */
    private function getTypesCable(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, libelType FROM type_cable ORDER BY libelType ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer la liste des statuts
     */
    private function getStatutsPowerbank(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, LibelStatut FROM statut ORDER BY LibelStatut ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer la liste des communes
     */
    private function getCommunes(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, nomCommune FROM commune ORDER BY nomCommune ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Liste des powerbanks
     * URL : index.php?controller=powerbank&action=index
     */
    public function index()
    {
        $this->requireLogin();

        $powerbanks = Powerbank::all();

        $this->render('powerbank/index', [
            'title'      => 'Liste des PowerBanks',
            'powerbanks' => $powerbanks,
        ], 'dashboard');
    }

    /**
     * Créer un powerbank
     * GET  : afficher le formulaire
     * POST : traiter le formulaire
     */
    public function create()
    {
        $this->requireLogin();

        $idFonction = $_SESSION['user_idFonction'] ?? null;
        if (!in_array((int)$idFonction, [1, 2], true)) {
            header('Location: index.php?controller=powerbank&action=index');
            exit;
        }

        // Toujours définir AVANT tout render()
        $typesCable = $this->getTypesCable();
        $statuts    = $this->getStatutsPowerbank();
        $communes   = $this->getCommunes();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = [
                'codePower'         => $_POST['codePower']         ?? '',
                'dateAcquis'        => $_POST['dateAcquis']        ?? '',
                'capacite'          => $_POST['capacite']          ?? '',
                'presentationEcran' => $_POST['presentationEcran'] ?? '',
                'idTypeCable'       => isset($_POST['idTypeCable']) ? (int)$_POST['idTypeCable'] : null,
                'idStatut'          => isset($_POST['idStatut'])    ? (int)$_POST['idStatut']    : null,
                'tarif'             => isset($_POST['tarif'])       ? (float)$_POST['tarif']     : null,
                'idCommune'         => isset($_POST['idCommune'])   ? (int)$_POST['idCommune']   : null,
            ];

            $errors = [];

            if ($data['codePower'] === '') {
                $errors[] = 'Le code du PowerBank est obligatoire.';
            }
            if ($data['dateAcquis'] === '') {
                $errors[] = 'La date d\'acquisition est obligatoire.';
            }
            if ($data['capacite'] === '') {
                $errors[] = 'La capacité est obligatoire.';
            }
            if ($data['tarif'] === null) {
                $errors[] = 'Le tarif est obligatoire.';
            }
            if ($data['idCommune'] === null) {
                $errors[] = 'La commune est obligatoire.';
            }

            if (!empty($errors)) {
                // En cas d’erreur, on renvoie aussi les listes à la vue
                $this->render('powerbank/form', [
                    'title'      => 'Ajouter un PowerBank',
                    'errors'     => $errors,
                    'old'        => $data,
                    'mode'       => 'create',
                    'typesCable' => $typesCable,
                    'statuts'    => $statuts,
                    'communes'   => $communes,
                ], 'dashboard');
                return;
            }

            $powerbank = new Powerbank($data);
            $powerbank->save();

            header('Location: index.php?controller=powerbank&action=index');
            exit;
        }

        // Affichage initial du formulaire
        $this->render('powerbank/form', [
            'title'      => 'Ajouter un PowerBank',
            'mode'       => 'create',
            'typesCable' => $typesCable,
            'statuts'    => $statuts,
            'communes'   => $communes,
        ], 'dashboard');
    }

    /**
     * Modifier un powerbank
     */
    public function edit()
    {
        $this->requireLogin();

        $idFonction = $_SESSION['user_idFonction'] ?? null;
        if (!in_array((int)$idFonction, [1, 2], true)) {
            header('Location: index.php?controller=powerbank&action=index');
            exit;
        }

        if (!isset($_GET['id'])) {
            die('ID du PowerBank manquant.');
        }

        $id = (int) $_GET['id'];
        $powerbank = Powerbank::find($id);

        if (!$powerbank) {
            die('PowerBank introuvable.');
        }

        // Listes pour les selects
        $typesCable = $this->getTypesCable();
        $statuts    = $this->getStatutsPowerbank();
        $communes   = $this->getCommunes();

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // On récupère toutes les valeurs
            $data = [
                'codePower'         => $_POST['codePower']         ?? $powerbank->codePower,
                'dateAcquis'        => $_POST['dateAcquis']        ?? $powerbank->dateAcquis,
                'capacite'          => $_POST['capacite']          ?? $powerbank->capacite,
                'presentationEcran' => $_POST['presentationEcran'] ?? $powerbank->presentationEcran,
                'idTypeCable'       => $_POST['idTypeCable']       ?? $powerbank->idTypeCable,
                'idStatut'          => $_POST['idStatut']          ?? $powerbank->idStatut,
                'tarif'             => $_POST['tarif']             ?? $powerbank->tarif,
                'idCommune'         => $_POST['idCommune']         ?? $powerbank->idCommune,
            ];

            // Nettoyage / cast
            $data['idTypeCable'] = ($data['idTypeCable'] === '' ? null : (int)$data['idTypeCable']);
            $data['idStatut']    = ($data['idStatut'] === ''    ? null : (int)$data['idStatut']);
            $data['tarif']       = ($data['tarif'] === ''       ? null : (float)$data['tarif']);
            $data['idCommune']   = ($data['idCommune'] === ''   ? null : (int)$data['idCommune']);

            // Validations simples
            if ($data['codePower'] === '') {
                $errors[] = 'Le code du PowerBank est obligatoire.';
            }
            if ($data['dateAcquis'] === '') {
                $errors[] = 'La date d\'acquisition est obligatoire.';
            }
            if ($data['capacite'] === '') {
                $errors[] = 'La capacité est obligatoire.';
            }
            if ($data['tarif'] === null) {
                $errors[] = 'Le tarif est obligatoire.';
            }
            if ($data['idStatut'] === null || $data['idStatut'] === 0) {
                $errors[] = 'Le statut du PowerBank est obligatoire.';
            }
            if ($data['idCommune'] === null) {
                $errors[] = 'La commune est obligatoire.';
            }

            // Optionnel : vérifier que l'idStatut existe bien dans la table statut
            if ($data['idStatut'] !== null) {
                $pdo = Database::getConnection();
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM statut WHERE id = :id");
                $stmt->execute(['id' => $data['idStatut']]);
                if ($stmt->fetchColumn() == 0) {
                    $errors[] = 'Le statut sélectionné est invalide.';
                }
            }

            if (!empty($errors)) {
                // On renvoie le formulaire avec erreurs + anciennes valeurs
                $this->render('powerbank/form', [
                    'title'      => 'Modifier un PowerBank',
                    'mode'       => 'edit',
                    'powerbank'  => $powerbank,
                    'old'        => $data,
                    'errors'     => $errors,
                    'typesCable' => $typesCable,
                    'statuts'    => $statuts,
                    'communes'   => $communes,
                ], 'dashboard');
                return;
            }

            // Si tout est OK, on met à jour l’objet
            $powerbank->codePower         = $data['codePower'];
            $powerbank->dateAcquis        = $data['dateAcquis'];
            $powerbank->capacite          = $data['capacite'];
            $powerbank->presentationEcran = $data['presentationEcran'];
            $powerbank->idTypeCable       = $data['idTypeCable'];
            $powerbank->idStatut          = $data['idStatut'];
            $powerbank->tarif             = $data['tarif'];
            $powerbank->idCommune         = $data['idCommune'];

            $powerbank->save();

            header('Location: index.php?controller=powerbank&action=index');
            exit;
        }

        // Affichage initial du formulaire
        $this->render('powerbank/form', [
            'title'      => 'Modifier un PowerBank',
            'mode'       => 'edit',
            'powerbank'  => $powerbank,
            'typesCable' => $typesCable,
            'statuts'    => $statuts,
            'communes'   => $communes,
        ], 'dashboard');
    }


    /**
     * Supprimer un powerbank
     */
    public function delete()
    {
        $this->requireLogin();

        $idFonction = $_SESSION['user_idFonction'] ?? null;
        if ((int)$idFonction !== 1) {
            header('Location: index.php?controller=powerbank&action=index');
            exit;
        }

        if (!isset($_GET['id'])) {
            die('ID du PowerBank manquant.');
        }

        $id = (int) $_GET['id'];

        Powerbank::delete($id);

        header('Location: index.php?controller=powerbank&action=index');
        exit;
    }
}
