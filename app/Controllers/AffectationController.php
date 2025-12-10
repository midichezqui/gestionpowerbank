<?php
// app/Controllers/AffectationController.php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../Models/Affectation.php';
require_once __DIR__ . '/../Models/Powerbank.php';

class AffectationController extends Controller
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
     * Liste des affectations
     * - Super admin (idFonction = 1) : toutes
     * - Admin (idFonction = 2)       : celles qu'il a créées
     * - Agent simple (autres)        : celles qui lui sont assignées aujourd'hui
     */
    public function index()
    {
        $this->requireLogin();

        $idAgentConnecte = (int)$_SESSION['user_id'];

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT idFonction FROM agent WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $idAgentConnecte]);
        $idFonction = (int)$stmt->fetchColumn();

        $role = 'simple';  // par défaut

        if ($idFonction === 1) {
            // Super admin
            $affectations   = Affectation::allWithJoins();
            $role           = 'super';
        } elseif ($idFonction === 2) {
            // Admin
            $affectations   = Affectation::allCreatedByAgent($idAgentConnecte);
            $role           = 'admin';
        } else {
            // Agent simple
            $affectations   = Affectation::allForAgentToday($idAgentConnecte);
            $role           = 'simple';
        }

        $isSuperAdmin   = ($role === 'super');
        $canCreate      = ($role === 'super' || $role === 'admin');
        $canDelete      = ($role === 'super' || $role === 'admin');
        $showCreatorCol = $isSuperAdmin;

        $this->render('affectation/index', [
            'title'          => 'Liste des affectations',
            'affectations'   => $affectations,
            'role'           => $role,
            'isSuperAdmin'   => $isSuperAdmin,
            'canCreate'      => $canCreate,
            'canDelete'      => $canDelete,
            'showCreatorCol' => $showCreatorCol,
        ], 'dashboard');
    }

    /**
     * Formulaire + enregistrement d'une nouvelle affectation
     * ⚠️ Réservé au super admin et admin
     */
    public function create()
    {
        $this->requireLogin();

        $pdo = Database::getConnection();
        $idAgentConnecte = (int)$_SESSION['user_id'];

        // Vérifier le rôle
        $stmt = $pdo->prepare("SELECT idFonction FROM agent WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $idAgentConnecte]);
        $idFonction = (int)$stmt->fetchColumn();

        if (!in_array($idFonction, [1, 2], true)) {
            // Agent simple : pas le droit de créer une affectation
            header('Location: index.php?controller=affectation&action=index');
            exit;
        }

        $errors = [];
        $old    = [];

        // Listes pour les <select>
        $agents = $pdo->query("
            SELECT id, nom, postnom, prenom 
            FROM agent 
            ORDER BY prenom, nom, postnom
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Communes (pour filtrer les powerbanks)
        $communes = $pdo->query("
            SELECT id, nomCommune
            FROM commune
            ORDER BY nomCommune
        ")->fetchAll(PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // ✅ Agent (champ simple)
            $idAgent = isset($_POST['idAgent']) ? (int)$_POST['idAgent'] : null;

            // ✅ Commune choisie pour cette affectation
            $idCommune = isset($_POST['idCommune']) ? (int)$_POST['idCommune'] : null;

            // ✅ Date d'affectation (datetime-local → Y-m-d H:i:s)
            $dateInput = $_POST['dateAffectation'] ?? '';
            if (!empty($dateInput)) {
                // format reçu : 2025-11-28T08:30
                $dateAffectation = str_replace('T', ' ', $dateInput) . ':00';
            } else {
                $dateAffectation = date('Y-m-d H:i:s');
            }

            // ✅ Powerbanks (plusieurs lignes)
            $idPowerArray    = $_POST['idPower']    ?? [];

            if (!is_array($idPowerArray)) {
                $idPowerArray = [];
            }

            // Validation basique
            if (!$idAgent) {
                $errors[] = "L'agent affecté est obligatoire.";
            }

            if (!$idCommune) {
                $errors[] = "La commune est obligatoire.";
            }

            // Vérifier qu'au moins un PowerBank est renseigné
            $hasValidLine = false;
            foreach ($idPowerArray as $pId) {
                $pId = (int)$pId;
                if ($pId > 0) {
                    $hasValidLine = true;
                    break;
                }
            }

            if (!$hasValidLine) {
                $errors[] = "Veuillez saisir au moins une ligne avec un PowerBank.";
            }

            // Règle métier : un agent ne peut pas être affecté à deux communes différentes le même jour
            if ($idAgent && $idCommune && $dateAffectation) {
                // On compare sur la date (jour) uniquement
                $sqlCommuneJour = "
                    SELECT DISTINCT c.id AS idCommune
                    FROM affectation a
                    INNER JOIN power_bank pb ON pb.id = a.idPower
                    INNER JOIN commune c      ON c.id = pb.idCommune
                    WHERE a.idAgent = :idAgent
                      AND DATE(a.dateAffectation) = DATE(:dateAffectation)
                ";

                $stmtComm = $pdo->prepare($sqlCommuneJour);
                $stmtComm->execute([
                    'idAgent'         => $idAgent,
                    'dateAffectation' => $dateAffectation,
                ]);

                $communesExistantes = $stmtComm->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($communesExistantes)) {
                    // L'agent a déjà des affectations ce jour-là
                    // Si au moins une commune existante est différente de celle choisie, on bloque
                    foreach ($communesExistantes as $idCommuneExistant) {
                        if ((int)$idCommuneExistant !== $idCommune) {
                            $errors[] = "Cet agent a déjà des affectations ce jour-là dans une autre commune. Il ne peut être affecté qu'à une seule commune par journée.";
                            break;
                        }
                    }
                }
            }

            $old = $_POST;

            if (!empty($errors)) {
                $this->render('affectation/form', [
                    'title'      => 'Nouvelle affectation',
                    'errors'     => $errors,
                    'old'        => $old,
                    'agents'     => $agents,
                    'communes'   => $communes,
                ], 'dashboard');
                return;
            }

            // ✅ Enregistrement de TOUTES les lignes valides (un PowerBank par affectation)
            foreach ($idPowerArray as $pId) {
                $pId = (int)$pId;

                if ($pId <= 0) {
                    continue; // on ignore les lignes incomplètes
                }

                $aff = new Affectation([
                    'dateAffectation' => $dateAffectation,
                    'idAgent'         => $idAgent,           // agent assigné
                    'idPower'         => $pId,
                    'idAgentCreate'   => $idAgentConnecte,   // agent connecté (créateur)
                ]);

                $aff->save();

                // 🔥 Mettre chaque powerbank en statut 3 (affecté)
                Powerbank::setStatut($pId, 3);
            }

            header('Location: index.php?controller=affectation&action=index');
            exit;
        }

        // GET : affichage du formulaire
        $this->render('affectation/form', [
            'title'      => 'Nouvelle affectation',
            'errors'     => $errors,
            'old'        => $old,
            'agents'     => $agents,
            'communes'   => $communes,
        ], 'dashboard');
    }

    /**
     * Suppression d'une affectation
     * ⚠️ Réservé au super admin et admin
     */
    public function delete()
    {
        $this->requireLogin();

        if (!isset($_GET['id'])) {
            die("ID affectation manquant.");
        }

        $id = (int)$_GET['id'];

        $pdo = Database::getConnection();
        $idAgentConnecte = (int)$_SESSION['user_id'];

        // Vérifier rôle
        $stmt = $pdo->prepare("SELECT idFonction FROM agent WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $idAgentConnecte]);
        $idFonction = (int)$stmt->fetchColumn();

        if (!in_array($idFonction, [1, 2], true)) {
            // Agent simple : pas le droit de supprimer
            header('Location: index.php?controller=affectation&action=index');
            exit;
        }

        // 🔁 Récupérer le PowerBank lié à cette affectation pour le remettre disponible
        $stmtPb = $pdo->prepare("SELECT idPower FROM affectation WHERE id = :id LIMIT 1");
        $stmtPb->execute(['id' => $id]);
        $idPower = $stmtPb->fetchColumn();

        if ($idPower) {
            // 2 = disponible
            Powerbank::setStatut((int)$idPower, 2);
        }

        Affectation::delete($id);

        header('Location: index.php?controller=affectation&action=index');
        exit;
    }

    /**
     * 🔹 API JSON appelée en AJAX pour charger les quartiers d'une commune
     * URL : index.php?controller=affectation&action=quartiersByCommune&idCommune=X
     */
    public function quartiersByCommune()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_GET['idCommune'])) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([]);
            exit;
        }

        $idCommune = (int)$_GET['idCommune'];

        $pdo = Database::getConnection();

        $sql = "
            SELECT 
                q.id,
                q.nomQuartier
            FROM quartier q
            WHERE q.idCommune = :idCommune
            ORDER BY q.nomQuartier
        ";

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute(['idCommune' => $idCommune]);
            $quartiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($quartiers);
            exit;

        } catch (PDOException $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([]);
            exit;
        }
    }

    /**
     * 🔹 API JSON pour charger les powerbanks disponibles d'une commune
     * URL : index.php?controller=affectation&action=powerbanksByCommune&idCommune=X
     */
    public function powerbanksByCommune()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_GET['idCommune'])) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([]);
            exit;
        }

        $idCommune = (int)$_GET['idCommune'];

        $pdo = Database::getConnection();

        $sql = "
            SELECT 
                pb.id,
                pb.codePower
            FROM power_bank pb
            WHERE pb.idCommune = :idCommune
              AND pb.idStatut  = 2
            ORDER BY pb.codePower
        ";

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute(['idCommune' => $idCommune]);
            $pbs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($pbs);
            exit;

        } catch (PDOException $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([]);
            exit;
        }
    }
}
