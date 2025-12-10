<?php
// app/Controllers/QuartierController.php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../Models/Quartier.php';

class QuartierController extends Controller
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

    private function getCommunes()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, nomCommune FROM commune ORDER BY nomCommune ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function index()
    {
        $this->requireLogin();

        $quartiers = Quartier::all();

        $this->render('quartier/index', [
            'title'     => 'Liste des quartiers',
            'quartiers' => $quartiers
        ], 'dashboard');
    }

    public function create()
    {
        $this->requireLogin();

        // Seuls admin (2) et super (1) peuvent créer un quartier
        $idFonction = $_SESSION['user_idFonction'] ?? null;
        if (!in_array((int)$idFonction, [1, 2], true)) {
            header('Location: index.php?controller=quartier&action=index');
            exit;
        }

        $communes = $this->getCommunes();
        $errors   = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = [
                'nomQuartier' => $_POST['nomQuartier'] ?? '',
                'idCommune'   => $_POST['idCommune']   ?? '',
            ];

            $data['idCommune'] = $data['idCommune'] === '' ? null : (int)$data['idCommune'];

            if ($data['nomQuartier'] === '')
                $errors[] = 'Le nom du quartier est obligatoire.';

            if ($data['idCommune'] === null)
                $errors[] = 'La commune est obligatoire.';

            if (!empty($errors)) {
                $this->render('quartier/form', [
                    'title'    => 'Ajouter un quartier',
                    'mode'     => 'create',
                    'errors'   => $errors,
                    'old'      => $data,
                    'communes' => $communes,
                ], 'dashboard');
                return;
            }

            $quartier = new Quartier($data);
            $quartier->save();

            header('Location: index.php?controller=quartier&action=index');
            exit;
        }

        $this->render('quartier/form', [
            'title'    => 'Ajouter un quartier',
            'mode'     => 'create',
            'communes' => $communes,
        ], 'dashboard');
    }

    public function edit()
    {
        $this->requireLogin();

        // Seuls admin (2) et super (1) peuvent modifier un quartier
        $idFonction = $_SESSION['user_idFonction'] ?? null;
        if (!in_array((int)$idFonction, [1, 2], true)) {
            header('Location: index.php?controller=quartier&action=index');
            exit;
        }

        if (!isset($_GET['id'])) die('ID quartier manquant.');
        $quartier = Quartier::find((int)$_GET['id']);

        if (!$quartier) die('Quartier introuvable.');

        $communes = $this->getCommunes();
        $errors   = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = [
                'nomQuartier' => $_POST['nomQuartier'] ?? $quartier->nomQuartier,
                'idCommune'   => $_POST['idCommune']   ?? $quartier->idCommune,
            ];

            $data['idCommune'] = $data['idCommune'] === '' ? null : (int)$data['idCommune'];

            if ($data['nomQuartier'] === '')
                $errors[] = 'Le nom du quartier est obligatoire.';

            if ($data['idCommune'] === null)
                $errors[] = 'La commune est obligatoire.';

            if (!empty($errors)) {
                $this->render('quartier/form', [
                    'title'    => 'Modifier un quartier',
                    'mode'     => 'edit',
                    'quartier' => $quartier,
                    'old'      => $data,
                    'errors'   => $errors,
                    'communes' => $communes
                ], 'dashboard');
                return;
            }

            $quartier->nomQuartier = $data['nomQuartier'];
            $quartier->idCommune   = $data['idCommune'];
            $quartier->save();

            header('Location: index.php?controller=quartier&action=index');
            exit;
        }

        $this->render('quartier/form', [
            'title'    => 'Modifier un quartier',
            'mode'     => 'edit',
            'quartier' => $quartier,
            'communes' => $communes
        ], 'dashboard');
    }

    public function delete()
    {
        $this->requireLogin();

        // Seul le super (1) peut supprimer un quartier
        $idFonction = $_SESSION['user_idFonction'] ?? null;
        if ((int)$idFonction !== 1) {
            header('Location: index.php?controller=quartier&action=index');
            exit;
        }

        if (!isset($_GET['id'])) die('ID quartier manquant.');

        Quartier::delete((int)$_GET['id']);

        header('Location: index.php?controller=quartier&action=index');
        exit;
    }
}
