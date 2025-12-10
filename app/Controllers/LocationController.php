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
     * Clients des quartiers liés aux communes des PowerBanks
     * affectés aujourd'hui à l'agent donné.
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
            FROM affectation a
            INNER JOIN power_bank pb ON pb.id        = a.idPower
            INNER JOIN commune co    ON co.id        = pb.idCommune
            INNER JOIN quartier q    ON q.idCommune  = co.id
            INNER JOIN clients c     ON c.idQuartier = q.id
            WHERE a.idAgent = :idAgent
              AND DATE(a.dateAffectation) = CURDATE()
              AND c.idEtat = 1
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
                pb.codePower  AS powerCode
            FROM affectation a
            INNER JOIN agent ag      ON ag.id = a.idAgent
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
     * ⚠️ Autorisé uniquement pour les agents simples (idFonction ≠ 1 et ≠ 2)
     */
    public function close()
    {
        $this->requireLogin();

        if (!isset($_GET['id'])) {
            die("ID location manquant.");
        }

        $pdo             = Database::getConnection();
        $idAgentConnecte = (int)$_SESSION['user_id'];

        // Vérifier le rôle de l'utilisateur connecté
        $stmt = $pdo->prepare("SELECT idFonction FROM agent WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $idAgentConnecte]);
        $idFonction = (int)$stmt->fetchColumn();

        // Si super admin (1) ou admin (2) → pas le droit de clôturer
        if (in_array($idFonction, [1, 2], true)) {
            header('Location: index.php?controller=location&action=index');
            exit;
        }

        $location = Location::find((int)$_GET['id']);
        if (!$location) {
            die("Location introuvable.");
        }

        // Sécurité supplémentaire : l’agent ne clôture que ses propres locations
        if ((int)$location->idAgent !== $idAgentConnecte) {
            header('Location: index.php?controller=location&action=index');
            exit;
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

       // Calcul de la pénalité simple
    // Calcul de la pénalité simple
        if ($hours > self::DUREE_FORFAIT) {
            // On ENREGISTRE la pénalité théorique, mais on ne l'ajoute pas encore à pt
            $location->penalite       = self::PENALITE_RETARD; // 2000
            $location->statutPenalite = 'due';                  // pénalité due

            // Mise à jour du statut de la pénalité AVEC l'id de la location !
            $stmt = $pdo->prepare("UPDATE location SET statutPenalite = 'due' WHERE id = :idLoc");
            $stmt->execute(['idLoc' => $location->id]);


        } else {
            $location->penalite       = 0;
            $location->statutPenalite = 'aucune';
        }

        // Le prix total reste le forfait tant que rien n'est payé
        $location->pt     = $montantBase;
        $location->statut = 'cloturee';

        $location->save();

        // Remettre le PowerBank en statut 2 (disponible)
        $stmt = $pdo->prepare("SELECT idPower FROM affectation WHERE id = :id");
        $stmt->execute(['id' => $location->idAffectation]);
        $idPower = $stmt->fetchColumn();

        if ($idPower) {
            Powerbank::setStatut((int)$idPower, 2);
        }

        header('Location: index.php?controller=location&action=index');
        exit;
    }

    public function marquerNonPaye()
    {
        $this->requireLogin();

        if (!isset($_GET['id'])) {
            die("ID location manquant.");
        }

        $location = Location::find((int)$_GET['id']);
        if (!$location) {
            die("Location introuvable.");
        }

        // On bloque uniquement si une pénalité est due
        if ($location->penalite > 0 && $location->statutPenalite === 'due') {
            $pdo = Database::getConnection();

            // Bloquer le client
            $stmt = $pdo->prepare("UPDATE clients SET idEtat = 2 WHERE id = :idClient");
            $stmt->execute(['idClient' => $location->idClient]);

            // Marquer statutPenalite comme non payé
            $stmt = $pdo->prepare("UPDATE location SET statutPenalite = 'non_paye' WHERE id = :id");
            $stmt->execute(['id' => $location->id]);
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

    //Payer penalité

    public function payerPenalite()
    {
        $this->requireLogin();

        if (!isset($_GET['id'])) {
            die("ID location manquant.");
        }

        $pdo             = Database::getConnection();
        $idAgentConnecte = (int)$_SESSION['user_id'];

        // Rôle de l'agent connecté
        $stmt = $pdo->prepare("SELECT idFonction FROM agent WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $idAgentConnecte]);
        $idFonction = (int)$stmt->fetchColumn();

        $location = Location::find((int)$_GET['id']);
        if (!$location) {
            die("Location introuvable.");
        }

        // Si pas de pénalité due / non payée => rien à faire ici
        if (!in_array($location->statutPenalite, ['due', 'non_paye'], true)) {
            header('Location: index.php?controller=location&action=index');
            exit;
        }

        // 🔍 État du client
        $stmtClientEtat = $pdo->prepare("SELECT idEtat FROM clients WHERE id = :idClient LIMIT 1");
        $stmtClientEtat->execute(['idClient' => $location->idClient]);
        $idEtatClient = (int)$stmtClientEtat->fetchColumn();
        $clientBloque = ($idEtatClient === 2);

        $isManager = in_array($idFonction, [1, 2], true); // 1 = super, 2 = admin

        // Si le client est BLOQUÉ, seul admin / super peuvent encaisser (double)
        if ($clientBloque && !$isManager) {
            header('Location: index.php?controller=location&action=index');
            exit;
        }

        // 🧮 Montant requis
        $montantRequis = $clientBloque
            ? $location->penalite * 2   // double pour débloquer
            : $location->penalite;      // simple pénalité

        $errors = [];
        $old    = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $montantPaye = isset($_POST['montantPaye']) ? (float)$_POST['montantPaye'] : 0;
            $datePaiement = !empty($_POST['datePaiement'])
                ? $_POST['datePaiement']
                : date('Y-m-d\TH:i');

            if ($montantPaye <= 0) {
                $errors[] = "Le montant payé est obligatoire.";
            }

            if ($montantPaye < $montantRequis) {
                $errors[] = "Le montant payé doit être au moins égal à " . number_format($montantRequis, 2) . " FC.";
            }

            $old = $_POST;

            if (!empty($errors)) {
                // Réaffichage du formulaire avec les erreurs
                $this->render('location/payer_penalite', [
                    'title'         => 'Paiement de la pénalité',
                    'location'      => $location,
                    'montantRequis' => $montantRequis,
                    'clientBloque'  => $clientBloque,
                    'errors'        => $errors,
                    'old'           => $old,
                ], 'dashboard');
                return;
            }

            // ✅ Mise à jour de la location
            $stmtUpdate = $pdo->prepare("
                UPDATE location
                SET 
                    pt                    = pt + :montantPaye,
                    montantPenalitePaye   = :montantPaye,
                    datePaiementPenalite  = :datePaiement,
                    statutPenalite        = 'paye'
                WHERE id = :id
            ");
            $stmtUpdate->execute([
                'montantPaye' => $montantPaye,
                'datePaiement'=> date('Y-m-d H:i:s', strtotime($datePaiement)),
                'id'          => $location->id,
            ]);

            // 🔓 Si le client était bloqué, on le débloque
            if ($clientBloque) {
                $stmtClient = $pdo->prepare("UPDATE clients SET idEtat = 1 WHERE id = :idClient");
                $stmtClient->execute(['idClient' => $location->idClient]);
            }

            header('Location: index.php?controller=location&action=index');
            exit;
        }

        // GET : affichage du formulaire
        $this->render('location/payer_penalite', [
            'title'         => 'Paiement de la pénalité',
            'location'      => $location,
            'montantRequis' => $montantRequis,
            'clientBloque'  => $clientBloque,
            'errors'        => $errors,
            'old'           => $old,
        ], 'dashboard');
    }




}
