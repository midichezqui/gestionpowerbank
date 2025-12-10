<?php
// app/Models/Location.php

require_once __DIR__ . '/../../core/Database.php';

class Location
{
    public $id;
    public $dateLocation;
    public $heureDebut;
    public $duree;
    public $heureFin;
    public $idAffectation;
    public $idClient;
    public $pt;
    public $statut;
    public $penalite;
    public $idAgent;

    // Labels pour affichage
    public $clientNom;
    public $agentNom;
    public $quartierNom;
    public $powerCode;
    public $clientEtat;
    public $statutPenalite;

    public function __construct(array $data = [])
    {
        $this->id            = $data['id'] ?? null;
        $this->dateLocation  = $data['dateLocation'] ?? null;
        $this->heureDebut    = $data['heureDebut'] ?? null;
        $this->duree         = $data['duree'] ?? 0;
        $this->heureFin      = $data['heureFin'] ?? null;
        $this->idAffectation = $data['idAffectation'] ?? null;
        $this->idClient      = $data['idClient'] ?? null;
        $this->pt            = $data['pt'] ?? 0;

        // nouveaux champs
        $this->statut   = $data['statut']   ?? 'demarree';
        $this->penalite = $data['penalite'] ?? 0;
        $this->idAgent  = $data['idAgent']  ?? null;

        $this->clientNom   = $data['clientNom']   ?? null;
        $this->agentNom    = $data['agentNom']    ?? null;
        $this->quartierNom = $data['quartierNom'] ?? null;
        $this->powerCode   = $data['powerCode']   ?? null;
        $this->clientEtat = $data['clientEtat'] ?? null;
         $this->statutPenalite = $data['statutPenalite'] ?? null;
    }

    /**
     * 🔹 Toutes les locations (Super admin)
     */
    public static function all(): array
    {
        $pdo = Database::getConnection();

        $sql = "
            SELECT 
                l.*,
                CONCAT(c.prenom, ' ', c.nom) AS clientNom,
                CONCAT(ag.prenom, ' ', ag.nom) AS agentNom,
                q.nomQuartier AS quartierNom,
                pb.codePower  AS powerCode,
                c.idEtat AS clientEtat
            FROM location l
            INNER JOIN clients c      ON c.id = l.idClient
            LEFT JOIN quartier q      ON q.id = c.idQuartier
            INNER JOIN affectation a  ON a.id = l.idAffectation
            INNER JOIN agent ag       ON ag.id = a.idAgent
            INNER JOIN power_bank pb  ON pb.id = a.idPower
            ORDER BY l.dateLocation DESC, l.id DESC
        ";

        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Location($row), $rows);
    }

    /**
     * 🔹 Locations d’un agent simple (idAgent = agent connecté)
     */
    public static function allForAgent(int $idAgent): array
    {
        $pdo = Database::getConnection();

        $sql = "
            SELECT 
                l.*,
                CONCAT(c.prenom, ' ', c.nom) AS clientNom,
                CONCAT(ag.prenom, ' ', ag.nom) AS agentNom,
                q.nomQuartier AS quartierNom,
                pb.codePower  AS powerCode,
                c.idEtat AS clientEtat
            FROM location l
            INNER JOIN clients c      ON c.id = l.idClient
            LEFT JOIN quartier q      ON q.id = c.idQuartier
            INNER JOIN affectation a  ON a.id = l.idAffectation
            INNER JOIN agent ag       ON ag.id = a.idAgent
            INNER JOIN power_bank pb  ON pb.id = a.idPower
            WHERE l.idAgent = :idAgent
            ORDER BY l.dateLocation DESC, l.id DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['idAgent' => $idAgent]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Location($row), $rows);
    }

    /**
     * 🔹 Locations des affectations créées par un admin
     * (a.idAgentCreate = admin connecté)
     */
    public static function allForAdminCreator(int $idAgentCreate): array
    {
        $pdo = Database::getConnection();

        $sql = "
            SELECT 
                l.*,
                CONCAT(c.prenom, ' ', c.nom) AS clientNom,
                CONCAT(ag.prenom, ' ', ag.nom) AS agentNom,
                q.nomQuartier AS quartierNom,
                pb.codePower  AS powerCode,
                c.idEtat AS clientEtat
            FROM location l
            INNER JOIN clients c      ON c.id = l.idClient
            LEFT JOIN quartier q      ON q.id = c.idQuartier
            INNER JOIN affectation a  ON a.id = l.idAffectation
            INNER JOIN agent ag       ON ag.id = a.idAgent
            INNER JOIN power_bank pb  ON pb.id = a.idPower
            WHERE a.idAgentCreate = :idAgentCreate
            ORDER BY l.dateLocation DESC, l.id DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['idAgentCreate' => $idAgentCreate]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Location($row), $rows);
    }

    public static function find(int $id): ?Location
    {
        $pdo = Database::getConnection();

        $sql = "
            SELECT 
                l.*,
                CONCAT(c.prenom, ' ', c.nom) AS clientNom,
                CONCAT(ag.prenom, ' ', ag.nom) AS agentNom,
                q.nomQuartier AS quartierNom,
                pb.codePower  AS powerCode
            FROM location l
            INNER JOIN clients c      ON c.id = l.idClient
            LEFT JOIN quartier q      ON q.id = c.idQuartier
            INNER JOIN affectation a  ON a.id = l.idAffectation
            INNER JOIN agent ag       ON ag.id = a.idAgent
            INNER JOIN power_bank pb  ON pb.id = a.idPower
            WHERE l.id = :id
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Location($row) : null;
    }

    public function save(): bool
    {
        $pdo = Database::getConnection();

        if ($this->id) {
            $sql = "
                UPDATE location
                SET dateLocation  = :dateLocation,
                    heureDebut    = :heureDebut,
                    duree         = :duree,
                    heureFin      = :heureFin,
                    idAffectation = :idAffectation,
                    idClient      = :idClient,
                    pt            = :pt,
                    statut        = :statut,
                    penalite      = :penalite,
                    idAgent       = :idAgent
                WHERE id = :id
            ";

            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                'dateLocation'  => $this->dateLocation,
                'heureDebut'    => $this->heureDebut,
                'duree'         => $this->duree,
                'heureFin'      => $this->heureFin,
                'idAffectation' => $this->idAffectation,
                'idClient'      => $this->idClient,
                'pt'            => $this->pt,
                'statut'        => $this->statut,
                'penalite'      => $this->penalite,
                'idAgent'       => $this->idAgent,
                'id'            => $this->id,
            ]);
        }

        $sql = "
            INSERT INTO location (
                dateLocation, heureDebut, duree, heureFin,
                idAffectation, idClient, pt, statut, penalite, idAgent
            ) VALUES (
                :dateLocation, :heureDebut, :duree, :heureFin,
                :idAffectation, :idClient, :pt, :statut, :penalite, :idAgent
            )
        ";

        $stmt = $pdo->prepare($sql);

        $ok = $stmt->execute([
            'dateLocation'  => $this->dateLocation,
            'heureDebut'    => $this->heureDebut,
            'duree'         => $this->duree,
            'heureFin'      => $this->heureFin,
            'idAffectation' => $this->idAffectation,
            'idClient'      => $this->idClient,
            'pt'            => $this->pt,
            'statut'        => $this->statut,
            'penalite'      => $this->penalite,
            'idAgent'       => $this->idAgent,
        ]);

        if ($ok) {
            $this->id = (int)$pdo->lastInsertId();
        }

        return $ok;
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM location WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Utilitaire pour changer le statut d’un PowerBank
     */
    public static function setStatut(int $idPower, int $newStatut)
    {
        $pdo = Database::getConnection();

        $sql = "UPDATE power_bank SET idStatut = :st WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            'st' => $newStatut,
            'id' => $idPower
        ]);
    }

    public static function performanceAgents($startDate, $endDate)
    {
        $pdo = Database::getConnection();

        $sql = "
            SELECT 
                ag.id,
                CONCAT(ag.prenom, ' ', ag.nom, ' ', ag.postnom) AS agentNom,
                COUNT(l.id) AS totalLocations,
                SUM(l.pt) AS totalChiffre,
                SUM(CASE WHEN l.statutPenalite = 'due' THEN 1 ELSE 0 END) AS penalitesNonPayees,
                SUM(CASE WHEN l.statutPenalite = 'paye' THEN 1 ELSE 0 END) AS penalitesPayees
            FROM agent ag
            LEFT JOIN location l ON l.idAgent = ag.id
                AND DATE(l.dateLocation) BETWEEN :d1 AND :d2
            GROUP BY ag.id
            HAVING COUNT(l.id) > 0
            ORDER BY totalChiffre DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'd1' => $startDate,
            'd2' => $endDate
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getRapportParDate(
        string $date1,
        string $date2,
        ?int $idAgent = null,
        ?int $idQuartier = null,
        ?string $filtrePenalite = null
    ) {
        $pdo = Database::getConnection();

        $sql = "
            SELECT 
                l.id,
                DATE(l.dateLocation) AS dateLocation,
                l.heureDebut,
                l.heureFin,
                l.duree,
                l.pt,
                l.montantPenalitePaye,
                l.statutPenalite,
                CONCAT(c.prenom, ' ', c.nom) AS clientNom,
                pb.codePower AS powerCode,
                q.nomQuartier AS quartierNom,
                CONCAT(ag.prenom, ' ', ag.nom) AS agentNom
            FROM location l
            INNER JOIN clients c       ON c.id = l.idClient
            LEFT JOIN quartier q       ON q.id = c.idQuartier
            INNER JOIN affectation af  ON af.id = l.idAffectation
            INNER JOIN power_bank pb   ON pb.id = af.idPower
            INNER JOIN agent ag        ON ag.id = l.idAgent
            WHERE DATE(l.dateLocation) BETWEEN :d1 AND :d2
        ";

        $params = [
            'd1' => $date1,
            'd2' => $date2,
        ];

        // Filtre agent
        if (!empty($idAgent)) {
            $sql .= " AND l.idAgent = :idAgent";
            $params['idAgent'] = $idAgent;
        }

        // Filtre quartier ignoré (quartier supprimé des affectations)

        // Filtre pénalité
        if ($filtrePenalite === 'avec') {
            $sql .= " AND l.penalite > 0";
        } elseif ($filtrePenalite === 'sans') {
            $sql .= " AND l.penalite = 0";
        } elseif ($filtrePenalite === 'non_paye') {
            $sql .= " AND l.statutPenalite = 'non_paye'";
        } elseif ($filtrePenalite === 'paye') {
            $sql .= " AND l.statutPenalite = 'paye'";
        }

        $sql .= " ORDER BY dateLocation ASC, l.heureDebut ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllAgents()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT id, CONCAT(prenom, ' ', nom) AS nomComplet
            FROM agent
            ORDER BY prenom, nom
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllQuartiers()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT id, nomQuartier
            FROM quartier
            ORDER BY nomQuartier
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getChiffreAffaires(string $date1, string $date2): array
    {
    $pdo = Database::getConnection();

    $sql = "
        SELECT 
            DATE(l.dateLocation) AS jour,
            COUNT(*)            AS nbLocations,
            COALESCE(SUM(l.pt), 0)        AS totalCA,
            COALESCE(SUM(l.penalite), 0)  AS totalPenalite,
            COALESCE(SUM(l.pt - l.penalite), 0) AS totalSansPenalite
        FROM location l
        WHERE DATE(l.dateLocation) BETWEEN :d1 AND :d2
        GROUP BY DATE(l.dateLocation)
        ORDER BY jour ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'd1' => $date1,
        'd2' => $date2,
    ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAffectationsJamaisLoue(string $date1, string $date2): array
    {
        $pdo = Database::getConnection();
        // On travaille uniquement sur la dernière date de location existante
        $sqlLastDate = "SELECT MAX(DATE(dateLocation)) AS lastDate FROM location";
        $lastDate = $pdo->query($sqlLastDate)->fetchColumn();

        // S'il n'y a encore aucune location en base, on ne retourne rien
        if (!$lastDate) {
            return [];
        }

        $sql = "
            SELECT
                a.id,
                a.dateAffectation,
                CONCAT(ag.prenom, ' ', ag.nom, ' ', ag.postnom) AS agentNom,
                pb.codePower  AS powerCode,
                co.nomCommune AS communeNom
            FROM affectation a
            INNER JOIN agent      ag  ON ag.id  = a.idAgent
            INNER JOIN power_bank pb  ON pb.id  = a.idPower
            LEFT JOIN commune    co  ON co.id  = pb.idCommune
            LEFT JOIN location    l   ON l.idAffectation = a.id
            WHERE l.id IS NULL
              AND (pb.idStatut IS NULL OR pb.idStatut <> 2)
              AND DATE(a.dateAffectation) = :lastDate
            ORDER BY a.dateAffectation DESC, a.id DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'lastDate' => $lastDate,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAffectationsJamaisLoueParPeriode(string $date1, string $date2, ?int $idAgent = null, ?int $idQuartier = null): array
    {
        $pdo = Database::getConnection();
        $sql = "
            SELECT
                a.id,
                a.dateAffectation,
                CONCAT(ag.prenom, ' ', ag.nom, ' ', ag.postnom) AS agentNom,
                pb.codePower  AS powerCode,
                co.nomCommune AS communeNom
            FROM affectation a
            INNER JOIN agent      ag  ON ag.id  = a.idAgent
            INNER JOIN power_bank pb  ON pb.id  = a.idPower
            LEFT JOIN commune    co  ON co.id  = pb.idCommune
            LEFT JOIN location    l   ON l.idAffectation = a.id
            WHERE l.id IS NULL
              AND (pb.idStatut IS NULL OR pb.idStatut <> 2)
              AND DATE(a.dateAffectation) BETWEEN :d1 AND :d2
        ";

        $params = [
            'd1' => $date1,
            'd2' => $date2,
        ];

        if (!empty($idAgent)) {
            $sql .= " AND a.idAgent = :idAgent";
            $params['idAgent'] = $idAgent;
        }

        if (!empty($idQuartier)) {
            $sql .= " AND q.id = :idQuartier";
            $params['idQuartier'] = $idQuartier;
        }

        $sql .= " ORDER BY a.dateAffectation DESC, a.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
