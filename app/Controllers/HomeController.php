<?php
// app/Controllers/HomeController.php

require_once __DIR__ . '/../../core/Controller.php';

class HomeController extends Controller
{
    public function dashboard()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=loginForm');
            exit;
        }

        $this->render('home/dashboard', [
            'title' => 'Tableau de bord',
        ], 'dashboard'); // si tu utilises le layout
    }
}
