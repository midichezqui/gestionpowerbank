<?php
// app/Controllers/CommuneController.php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../Models/Commune.php';

class CommuneController extends Controller
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

    public function index()
    {
        $this->requireLogin();

        $communes = Commune::all();

        $this->render('commune/index', [
            'title'    => 'Liste des communes',
            'communes' => $communes
        ], 'dashboard');
    }

    public function create()
    {
        $this->requireLogin();

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nomCommune = trim($_POST['nomCommune'] ?? '');

            if ($nomCommune === '') {
                $errors[] = "Le nom de la commune est obligatoire.";
            }

            if (!empty($errors)) {
                $this->render('commune/form', [
                    'title'      => 'Ajouter une commune',
                    'mode'       => 'create',
                    'errors'     => $errors,
                    'old'        => ['nomCommune' => $nomCommune]
                ], 'dashboard');
                return;
            }

            $commune = new Commune([
                'nomCommune' => $nomCommune
            ]);
            $commune->save();

            header('Location: index.php?controller=commune&action=index');
            exit;
        }

        $this->render('commune/form', [
            'title' => 'Ajouter une commune',
            'mode'  => 'create'
        ], 'dashboard');
    }

    public function edit()
    {
        $this->requireLogin();

        if (!isset($_GET['id'])) die("ID manquant.");
        $commune = Commune::find((int)$_GET['id']);

        if (!$commune) die("Commune introuvable.");

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nomCommune = trim($_POST['nomCommune'] ?? '');

            if ($nomCommune === '') {
                $errors[] = "Le nom de la commune est obligatoire.";
            }

            if (!empty($errors)) {
                $this->render('commune/form', [
                    'title'    => 'Modifier une commune',
                    'mode'     => 'edit',
                    'errors'   => $errors,
                    'old'      => ['nomCommune' => $nomCommune],
                    'commune'  => $commune
                ], 'dashboard');
                return;
            }

            $commune->nomCommune = $nomCommune;
            $commune->save();

            header('Location: index.php?controller=commune&action=index');
            exit;
        }

        $this->render('commune/form', [
            'title'   => 'Modifier une commune',
            'mode'    => 'edit',
            'commune' => $commune
        ], 'dashboard');
    }

    public function delete()
    {
        $this->requireLogin();

        if (!isset($_GET['id'])) die("ID manquant.");

        Commune::delete((int)$_GET['id']);

        header('Location: index.php?controller=commune&action=index');
        exit;
    }
}
