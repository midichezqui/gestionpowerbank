<?php
// app/Controllers/LocationController.php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../Models/Location.php';
require_once __DIR__ . '/../Models/Powerbank.php';

class LocationController extends Controller
{
    // Forfait & pénalité
    private const TARIF_FORFAIT   = 1000; // montant payé au démarrage pour 4h
    private const DUREE_FORFAIT   = 4;    // 4 heures
    private const PENALITE_RETARD = 2000; // amende si dépasse 4h

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
     * Clients des quartiers concernés par les affectations
     * de l'agent connecté pour la date du jour
     */
    private function getClientsForAgentToday(int $idAgent): array
    {
        $pdo = Database::getConnection();

        $sql = "
            SELECT DISTINCT
                c.id,
                c.nom,
                c.postnom,
                c.prenom,
                c.telephone,
                q.nomQuartier
            FROM clients c
            INNER JOIN quartier q
                ON q.id = c.idQuartier
            INNER JOIN affectation a
                ON a.idQuartier = c.idQuartier
            WHERE a.idAgent = :idAgent
              AND DATE(a.dateAffectation) = CURDATE()
            ORDER BY c.prenom, c.nom, c.postnom
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['idAgent' => $idAgent]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Affectations d'un agent pour une date donnée
     */
    private function getAffectationsForUserAndDate(int $idAgent, string $dateYmd): array
    {
        $pdo = Database::getConnection();

        $sql = "
            SELECT 
                a.id,
                CONCAT(ag.prenom, ' ', ag.nom, ' ', ag.postnom) AS agentNom,
                q.nomQuartier AS quartierNom,
                pb.codePower  AS powerCode
            FROM affectation a
            INNER JOIN agent ag      ON ag.id = a.idAgent
            INNER JOIN quartier q    ON q.id = a.idQuartier
            INNER JOIN power_bank pb ON pb.id = a.idPower
            WHERE a.idAgent = :idAgent
              AND DATE(a.dateAffectation) = :d
              AND pb.idStatut = 3
            ORDER BY a.dateAffectation DESC, agentNom
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'idAgent' => $idAgent,
            'd'       => $dateYmd,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Affectations de l'agent pour AUJOURD'HUI
     */
    private function getAffectationsForToday(int $idAgent): array
    {
        $today = date('Y-m-d');
        return $this->getAffectationsForUserAndDate($idAgent, $today);
    }

    /**
     * 🔹 Liste des locations avec filtrage par rôle
     */
    public function index()
    {
        $this->requireLogin();

        $pdo             = Database::getConnection();
        $idAgentConnecte = (int)$_SESSION['user_id'];

        // Récupérer le rôle via idFonction
        $stmt = $pdo->prepare("SELECT idFonction FROM agent WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $idAgentConnecte]);
        $idFonction = (int)$stmt->fetchColumn();

        $role = 'simple';
        if ($idFonction === 1) {
            // Super admin → toutes les locations
            $locations = Location::all();
            $role      = 'super';
        } elseif ($idFonction === 2) {
            // Admin → locations des affectations qu’il a créées
            $locations = Location::allForAdminCreator($idAgentConnecte);
            $role      = 'admin';
        } else {
            // Agent simple → ses propres locations
            $locations = Location::allForAgent($idAgentConnecte);
            $role      = 'simple';
        }

        $this->render('location/index', [
            'title'     => 'Locations de PowerBank',
            'locations' => $locations,
            'role'      => $role,
        ], 'dashboard');
    }

    /**
     * Démarrer une location
     */
    public function create()
    {
        $this->requireLogin();

        $errors = [];
        $old    = [];

        // ID de l'agent connecté
        $idAgentSession  = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $idAgentConnecte = $idAgentSession;

        if (!$idAgentSession) {
            header('Location: index.php?controller=auth&action=loginForm');
            exit;
        }

        // Date du jour
        $today = date('Y-m-d');

        // Clients des quartiers concernés par les affectations de l'agent aujourd'hui
        $clients      = $this->getClientsForAgentToday($idAgentSession);

        // Affectations de l'agent pour aujourd'hui
        $affectations = $this->getAffectationsForToday($idAgentSession);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $idClient      = isset($_POST['idClient']) ? (int)$_POST['idClient'] : null;
            $idAffectation = isset($_POST['idAffectation']) ? (int)$_POST['idAffectation'] : null;

            // Heure de début (facultative, sinon heure actuelle)
            $heureDebut = $_POST['heureDebut'] ?? date('H:i:s');

            // 🔸 Calcul de l'heure de fin théorique = début + 4 heures
            $timestampDebut    = strtotime($heureDebut);
            $timestampFin      = $timestampDebut + (4 * 3600);
            $heureFinTheorique = date('H:i:s', $timestampFin);

            if (!$idClient) {
                $errors[] = "Le client est obligatoire.";
            }

            if (!$idAffectation) {
                $errors[] = "L'affectation (quartier + powerbank) est obligatoire.";
            }

            $old = $_POST;

            if (!empty($errors)) {
                $this->render('location/form', [
                    'title'        => 'Nouvelle location',
                    'mode'         => 'create',
                    'errors'       => $errors,
                    'old'          => $old,
                    'clients'      => $clients,
                    'affectations' => $affectations,
                    'today'        => $today,
                ], 'dashboard');
                return;
            }

            // Récupérer le powerbank lié à l'affectation
            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare("SELECT idPower FROM affectation WHERE id = :id");
            $stmt->execute(['id' => $idAffectation]);
            $idPower = $stmt->fetchColumn();

            // Création de la location
            $location = new Location([
                'dateLocation'  => $today,
                'heureDebut'    => $heureDebut,
                'duree'         => 0,
                // 🔸 On enregistre la fin forfaitaire (4h après le début)
                'heureFin'      => $heureFinTheorique,
                'idAffectation' => $idAffectation,
                'idClient'      => $idClient,
                'idAgent'       => $idAgentConnecte,
                'pt'            => self::TARIF_FORFAIT, // 1000 pour 4 heures
                'statut'        => 'demarree',
                'penalite'      => 0,
            ]);

            $location->save();

            // Passer le PowerBank en statut 1 (en location / occupé)
            if ($idPower) {
                Powerbank::setStatut((int)$idPower, 1);
            }

            header('Location: index.php?controller=location&action=index');
            exit;
        }

        // GET : affichage initial
        $this->render('location/form', [
            'title'        => 'Nouvelle location',
            'mode'         => 'create',
            'clients'      => $clients,
            'affectations' => $affectations,
            'errors'       => $errors,
            'old'          => $old,
            'today'        => $today,
        ], 'dashboard');
    }

    /**
     * Clôturer une location : calcule durée, heureFin, pt (avec pénalité si > 4h)
     * + remet le PowerBank en statut 2 (disponible)
     */
    public function close()
    {
        $this->requireLogin();

        if (!isset($_GET['id'])) {
            die("ID location manquant.");
        }

        $location = Location::find((int)$_GET['id']);
        if (!$location) {
            die("Location introuvable.");
        }

        // Si déjà clôturée, on ne refait pas le calcul
        if ($location->statut === 'cloturee' || $location->duree > 0) {
            header('Location: index.php?controller=location&action=index');
            exit;
        }

        // Correction format date/heure
        $rawDate  = $location->dateLocation;       // ex: "2025-11-27 00:00:00"
        $onlyDate = substr($rawDate, 0, 10);       // "2025-11-27"

        // Début = date (sans heure) + heureDebut
        $start = new DateTime($onlyDate . ' ' . $location->heureDebut);
        $end   = new DateTime();   // maintenant

        $diffSeconds = $end->getTimestamp() - $start->getTimestamp();
        if ($diffSeconds < 0) $diffSeconds = 0;

        // Durée en heures, arrondie à l'entier supérieur
        $hours = (int)ceil($diffSeconds / 3600);
        if ($hours < 1) $hours = 1;

        $location->duree    = $hours;
        $location->heureFin = $end->format('H:i:s');

        // Montant de base (forfait payé au départ)
        $montantBase = (float)$location->pt;
        if ($montantBase <= 0) {
            $montantBase = self::TARIF_FORFAIT;
        }

        // Calcul de la pénalité
        $penalite = 0;
        if ($hours > self::DUREE_FORFAIT) {
            $penalite = self::PENALITE_RETARD;
        }

        $location->penalite = $penalite;
        $location->pt       = $montantBase + $penalite;
        $location->statut   = 'cloturee';

        $location->save();

        // Remettre le PowerBank en statut 2 (disponible)
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("SELECT idPower FROM affectation WHERE id = :id");
        $stmt->execute(['id' => $location->idAffectation]);
        $idPower = $stmt->fetchColumn();

        if ($idPower) {
            Powerbank::setStatut((int)$idPower, 2);
        }

        header('Location: index.php?controller=location&action=index');
        exit;
    }

    /**
     * Supprimer une location
     */
    public function delete()
    {
        $this->requireLogin();

        if (!isset($_GET['id'])) {
            die("ID location manquant.");
        }

        Location::delete((int)$_GET['id']);

        header('Location: index.php?controller=location&action=index');
        exit;
    }
}
