<?php
// app/Controllers/FonctionController.php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../Models/Fonction.php';

class FonctionController extends Controller
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

    /**
     * Liste
     * URL : index.php?controller=fonction&action=index
     */
    public function index()
    {
        $this->requireLogin();

        $fonctions = Fonction::all();

        $this->render('fonction/index', [
            'title'     => 'Liste des fonctions',
            'fonctions' => $fonctions,
        ], 'dashboard');
    }

    /**
     * Créer
     */
    public function create()
    {
        $this->requireLogin();

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $libelFonction = trim($_POST['libelFonction'] ?? '');

            if ($libelFonction === '') {
                $errors[] = 'Le libellé de la fonction est obligatoire.';
            }

            if (!empty($errors)) {
                $this->render('fonction/form', [
                    'title'  => 'Ajouter une fonction',
                    'mode'   => 'create',
                    'errors' => $errors,
                    'old'    => ['libelFonction' => $libelFonction],
                ], 'dashboard');
                return;
            }

            $fonction = new Fonction(['libelFonction' => $libelFonction]);
            $fonction->save();

            header('Location: index.php?controller=fonction&action=index');
            exit;
        }

        $this->render('fonction/form', [
            'title' => 'Ajouter une fonction',
            'mode'  => 'create',
        ], 'dashboard');
    }

    /**
     * Modifier
     */
    public function edit()
    {
        $this->requireLogin();

        if (!isset($_GET['id'])) {
            die('ID fonction manquant.');
        }

        $fonction = Fonction::find((int)$_GET['id']);

        if (!$fonction) {
            die('Fonction introuvable.');
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $libelFonction = trim($_POST['libelFonction'] ?? '');

            if ($libelFonction === '') {
                $errors[] = 'Le libellé de la fonction est obligatoire.';
            }

            if (!empty($errors)) {
                $this->render('fonction/form', [
                    'title'    => 'Modifier une fonction',
                    'mode'     => 'edit',
                    'errors'   => $errors,
                    'old'      => ['libelFonction' => $libelFonction],
                    'fonction' => $fonction,
                ], 'dashboard');
                return;
            }

            $fonction->libelFonction = $libelFonction;
            $fonction->save();

            header('Location: index.php?controller=fonction&action=index');
            exit;
        }

        $this->render('fonction/form', [
            'title'    => 'Modifier une fonction',
            'mode'     => 'edit',
            'fonction' => $fonction,
        ], 'dashboard');
    }

    /**
     * Supprimer
     */
    public function delete()
    {
        $this->requireLogin();

        if (!isset($_GET['id'])) {
            die('ID fonction manquant.');
        }

        Fonction::delete((int)$_GET['id']);

        header('Location: index.php?controller=fonction&action=index');
        exit;
    }
}
