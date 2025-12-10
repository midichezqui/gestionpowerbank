<?php
// app/Controllers/DashboardController.php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Database.php';

class DashboardController extends Controller
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

        $pdo             = Database::getConnection();
        $idAgentConnecte = (int)$_SESSION['user_id'];

        // 🔹 Récupérer le rôle via idFonction
        $stmt = $pdo->prepare("SELECT idFonction FROM agent WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $idAgentConnecte]);
        $idFonction = (int)$stmt->fetchColumn();

        $role = 'simple';
        if ($idFonction === 1) {
            $role = 'super';
        } elseif ($idFonction === 2) {
            $role = 'admin';
        }

        // 1) PowerBanks disponibles / utilisables
        if ($role === 'super' || $role === 'admin') {
            // Super admin / admin : nombre total de powerbanks en statut 2 (disponibles)
            $sqlDispo = "SELECT COUNT(*) FROM power_bank WHERE idStatut = 2";
            $powerbanksDisponibles = (int)$pdo->query($sqlDispo)->fetchColumn();
        } else {
            // Agent simple : uniquement les powerbanks qui lui sont affectés aujourd'hui
            // et qui ne sont pas encore loués
            $sqlDispo = "
                SELECT COUNT(*)
                FROM affectation a
                INNER JOIN power_bank pb ON pb.id = a.idPower
                LEFT JOIN location l     ON l.idAffectation = a.id
                    AND DATE(l.dateLocation) = CURDATE()
                    AND l.statut = 'demarree'
                WHERE a.idAgent = :idAgent
                  AND DATE(a.dateAffectation) = CURDATE()
                  AND l.id IS NULL
            ";

            $stmtDispo = $pdo->prepare($sqlDispo);
            $stmtDispo->execute(['idAgent' => $idAgentConnecte]);
            $powerbanksDisponibles = (int)$stmtDispo->fetchColumn();
        }

        // ==========================
        // 2) Locations en cours
        // ==========================
        if ($role === 'super') {
            // Super admin : toutes les locations démarrées
            $sqlEnCours = "
                SELECT COUNT(*)
                FROM location l
                WHERE l.statut = 'demarree'
            ";
            $locationsEnCours = (int)$pdo->query($sqlEnCours)->fetchColumn();

        } elseif ($role === 'admin') {
            // Admin : locations des affectations qu’il a créées
            $sqlEnCours = "
                SELECT COUNT(*)
                FROM location l
                INNER JOIN affectation a ON a.id = l.idAffectation
                WHERE l.statut = 'demarree'
                  AND a.idAgentCreate = :idAgent
            ";
            $stmtEnCours = $pdo->prepare($sqlEnCours);
            $stmtEnCours->execute(['idAgent' => $idAgentConnecte]);
            $locationsEnCours = (int)$stmtEnCours->fetchColumn();

        } else {
            // Agent simple : seulement ses locations
            $sqlEnCours = "
                SELECT COUNT(*)
                FROM location l
                WHERE l.statut = 'demarree'
                  AND l.idAgent = :idAgent
            ";
            $stmtEnCours = $pdo->prepare($sqlEnCours);
            $stmtEnCours->execute(['idAgent' => $idAgentConnecte]);
            $locationsEnCours = (int)$stmtEnCours->fetchColumn();
        }

        // ==========================
        // 3) Retards
        // ==========================
        if ($role === 'super') {
            $sqlRetards = "
                SELECT COUNT(*)
                FROM location l
                WHERE l.statut = 'demarree'
                  AND DATE_ADD(
                        CONCAT(DATE(l.dateLocation), ' ', l.heureDebut),
                        INTERVAL 4 HOUR
                      ) < NOW()
            ";
            $retards = (int)$pdo->query($sqlRetards)->fetchColumn();

        } elseif ($role === 'admin') {
            $sqlRetards = "
                SELECT COUNT(*)
                FROM location l
                INNER JOIN affectation a ON a.id = l.idAffectation
                WHERE l.statut = 'demarree'
                  AND a.idAgentCreate = :idAgent
                  AND DATE_ADD(
                        CONCAT(DATE(l.dateLocation), ' ', l.heureDebut),
                        INTERVAL 4 HOUR
                      ) < NOW()
            ";
            $stmtRet = $pdo->prepare($sqlRetards);
            $stmtRet->execute(['idAgent' => $idAgentConnecte]);
            $retards = (int)$stmtRet->fetchColumn();

        } else {
            $sqlRetards = "
                SELECT COUNT(*)
                FROM location l
                WHERE l.statut = 'demarree'
                  AND l.idAgent = :idAgent
                  AND DATE_ADD(
                        CONCAT(DATE(l.dateLocation), ' ', l.heureDebut),
                        INTERVAL 4 HOUR
                      ) < NOW()
            ";
            $stmtRet = $pdo->prepare($sqlRetards);
            $stmtRet->execute(['idAgent' => $idAgentConnecte]);
            $retards = (int)$stmtRet->fetchColumn();
        }

        // ==========================
        // 4) Recettes du jour
        // ==========================
        if ($role === 'super') {
            $sqlRecettes = "
                SELECT COALESCE(SUM(l.pt), 0)
                FROM location l
                WHERE DATE(l.dateLocation) = CURDATE()
            ";
            $recettesJour = (float)$pdo->query($sqlRecettes)->fetchColumn();

        } elseif ($role === 'admin') {
            $sqlRecettes = "
                SELECT COALESCE(SUM(l.pt), 0)
                FROM location l
                INNER JOIN affectation a ON a.id = l.idAffectation
                WHERE DATE(l.dateLocation) = CURDATE()
                  AND a.idAgentCreate = :idAgent
            ";
            $stmtRec = $pdo->prepare($sqlRecettes);
            $stmtRec->execute(['idAgent' => $idAgentConnecte]);
            $recettesJour = (float)$stmtRec->fetchColumn();

        } else {
            $sqlRecettes = "
                SELECT COALESCE(SUM(l.pt), 0)
                FROM location l
                WHERE DATE(l.dateLocation) = CURDATE()
                  AND l.idAgent = :idAgent
            ";
            $stmtRec = $pdo->prepare($sqlRecettes);
            $stmtRec->execute(['idAgent' => $idAgentConnecte]);
            $recettesJour = (float)$stmtRec->fetchColumn();
        }

        // ==========================
        // 5) Dernières locations (5 dernières)
        // ==========================
        if ($role === 'super') {
            $sqlLast = "
                SELECT 
                    l.*,
                    CONCAT(c.prenom, ' ', c.nom, ' ', c.postnom) AS clientNom,
                    pb.codePower AS powerCode
                FROM location l
                INNER JOIN clients c      ON c.id = l.idClient
                INNER JOIN affectation a  ON a.id = l.idAffectation
                INNER JOIN power_bank pb  ON pb.id = a.idPower
                ORDER BY l.id DESC
                LIMIT 5
            ";
            $stmtLast = $pdo->query($sqlLast);
            $lastLocations = $stmtLast->fetchAll(PDO::FETCH_ASSOC);

        } elseif ($role === 'admin') {
            $sqlLast = "
                SELECT 
                    l.*,
                    CONCAT(c.prenom, ' ', c.nom, ' ', c.postnom) AS clientNom,
                    pb.codePower AS powerCode
                FROM location l
                INNER JOIN clients c      ON c.id = l.idClient
                INNER JOIN affectation a  ON a.id = l.idAffectation
                INNER JOIN power_bank pb  ON pb.id = a.idPower
                WHERE a.idAgentCreate = :idAgent
                ORDER BY l.id DESC
                LIMIT 5
            ";
            $stmtLast = $pdo->prepare($sqlLast);
            $stmtLast->execute(['idAgent' => $idAgentConnecte]);
            $lastLocations = $stmtLast->fetchAll(PDO::FETCH_ASSOC);

        } else {
            $sqlLast = "
                SELECT 
                    l.*,
                    CONCAT(c.prenom, ' ', c.nom, ' ', c.postnom) AS clientNom,
                    pb.codePower AS powerCode
                FROM location l
                INNER JOIN clients c      ON c.id = l.idClient
                INNER JOIN affectation a  ON a.id = l.idAffectation
                INNER JOIN power_bank pb  ON pb.id = a.idPower
                WHERE l.idAgent = :idAgent
                ORDER BY l.id DESC
                LIMIT 5
            ";
            $stmtLast = $pdo->prepare($sqlLast);
            $stmtLast->execute(['idAgent' => $idAgentConnecte]);
            $lastLocations = $stmtLast->fetchAll(PDO::FETCH_ASSOC);
        }

        // Envoi à la vue
        $this->render('home/dashboard', [
            'title'                 => 'Tableau de bord',
            'powerbanksDisponibles' => $powerbanksDisponibles,
            'locationsEnCours'      => $locationsEnCours,
            'retards'               => $retards,
            'recettesJour'          => $recettesJour,
            'lastLocations'         => $lastLocations,
            'role'                  => $role, // si tu veux l’utiliser plus tard dans la vue
        ], 'dashboard');
    }
}
