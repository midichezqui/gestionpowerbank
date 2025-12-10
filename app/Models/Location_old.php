<?php
// app/Models/Location.php

require_once __DIR__ . '/../../core/Database.php';

class Location
{
    public $id;
    public $dateLocation;
    public $heureDebut;
    public $heureFin;
    public $duree;
    public $pt;
    public $penalite;
    public $statut;
    public $idAffectation;
    public $idClient;
    public $idAgent;

    // Nouveaux champs pénalité
    public $statutPenalite;         // 'aucune', 'due', 'non_paye', 'paye'
    public $montantPenalitePaye;    // DECIMAL
    public $datePaiementPenalite;   // DATETIME / NULL

    // Champs "virtuels" pour les jointures
    public $clientNom;
    public $powerCode;
    public $quartierNom;

    public function __construct(array $data = [])
    {
        $this->id                    = $data['id'] ?? null;
        $this->dateLocation          = $data['dateLocation'] ?? $data['date_location'] ?? null;
        $this->heureDebut            = $data['heureDebut'] ?? $data['heure_debut'] ?? null;
        $this->heureFin              = $data['heureFin'] ?? $data['heure_fin'] ?? null;
        $this->duree                 = $data['duree'] ?? 0;
        $this->pt                    = $data['pt'] ?? 0;
        $this->penalite              = $data['penalite'] ?? 0;
        $this->statut                = $data['statut'] ?? 'demarree';
        $this->idAffectation         = $data['idAffectation'] ?? $data['id_affectation'] ?? null;
        $this->idClient              = $data['idClient'] ?? $data['id_client'] ?? null;
        $this->idAgent               = $data['idAgent'] ?? $data['id_agent'] ?? null;

        $this->statutPenalite        = $data['statutPenalite'] ?? $data['statut_penalite'] ?? 'aucune';
        $this->montantPenalitePaye   = $data['montantPenalitePaye'] ?? $data['montant_penalite_paye'] ?? 0;
        $this->datePaiementPenalite  = $data['datePaiementPenalite'] ?? $data['date_paiement_penalite'] ?? null;

        $this->clientNom             = $data['clientNom'] ?? null;
        $this->powerCode             = $data['powerCode'] ?? null;
        $this->quartierNom           = $data['quartierNom'] ?? null;
    }

    private static function pdo()
    {
        return Database::getConnection();
    }

    /** Hydrate depuis un tableau PDO */
    private static function fromRow(array $row): Location
    {
        return new Location($row);
    }

    /** Récupérer une location par ID */
    public static function find(int $id): ?Location
    {
        $pdo = self::pdo();
        $sql = "
            SELECT 
                l.*,
                CONCAT(c.prenom, ' ', c.nom, ' ', c.postnom) AS clientNom,
                pb.codePower AS powerCode,
                q.nomQuartier AS quartierNom
            FROM location l
            INNER JOIN clients c      ON c.id = l.idClient
            INNER JOIN affectation a  ON a.id = l.idAffectation
            INNER JOIN quartier q     ON q.id = a.idQuartier
            INNER JOIN power_bank pb  ON pb.id = a.idPower
            WHERE l.id = :id
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? self::fromRow($row) : null;
    }

    /** Toutes les locations (vue super admin) */
    public static function all(): array
    {
        $pdo = self::pdo();
        $sql = "
            SELECT 
                l.*,
                CONCAT(c.prenom, ' ', c.nom, ' ', c.postnom) AS clientNom,
                pb.codePower AS powerCode,
                q.nomQuartier AS quartierNom
            FROM location l
            INNER JOIN clients c      ON c.id = l.idClient
            INNERINNER JOIN affectation a  ON a.id = l.idAffectation
            INNER JOIN quartier q     ON q.id = a.idQuartier
            INNER JOIN power_bank pb  ON pb.id = a.idPower
            ORDER BY l.id DESC
        ";
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([self::class, 'fromRow'], $rows);
    }

    /** Locations de l'agent simple (du jour ou pas, à adapter selon ton besoin) */
    public static function allForAgent(int $idAgent): array
    {
        $pdo = self::pdo();
        $sql = "
            SELECT 
                l.*,
                CONCAT(c.prenom, ' ', c.nom, ' ', c.postnom) AS clientNom,
                pb.codePower AS powerCode,
                q.nomQuartier AS quartierNom
            FROM location l
            INNER JOIN clients c      ON c.id = l.idClient
            INNER JOIN affectation a  ON a.id = l.idAffectation
            INNER JOIN quartier q     ON q.id = a.idQuartier
            INNER JOIN power_bank pb  ON pb.id = a.idPower
            WHERE l.idAgent = :idAgent
            ORDER BY l.id DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['idAgent' => $idAgent]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([self::class, 'fromRow'], $rows);
    }

    /** Locations pour un admin (affectations qu’il a créées) */
    public static function allForAdminCreator(int $idAdmin): array
    {
        $pdo = self::pdo();
        $sql = "
            SELECT 
                l.*,
                CONCAT(c.prenom, ' ', c.nom, ' ', c.postnom) AS clientNom,
                pb.codePower AS powerCode,
                q.nomQuartier AS quartierNom
            FROM location l
            INNER JOIN clients c      ON c.id = l.idClient
            INNER JOIN affectation a  ON a.id = l.idAffectation
            INNER JOIN quartier q     ON q.id = a.idQuartier
            INNER JOIN power_bank pb  ON pb.id = a.idPower
            WHERE a.idAgentCreate = :idAdmin
            ORDER BY l.id DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['idAdmin' => $idAdmin]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([self::class, 'fromRow'], $rows);
    }

    /** Historique des pénalités (tout ce qui n’est pas ‘aucune’) */
    public static function allWithPenalites(): array
    {
        $pdo = self::pdo();
        $sql = "
            SELECT 
                l.*,
                CONCAT(c.prenom, ' ', c.nom, ' ', c.postnom) AS clientNom,
                pb.codePower AS powerCode,
                q.nomQuartier AS quartierNom
            FROM location l
            INNER JOIN clients c      ON c.id = l.idClient
            INNER JOIN affectation a  ON a.id = l.idAffectation
            INNER JOIN quartier q     ON q.id = a.idQuartier
            INNER JOIN power_bank pb  ON pb.id = a.idPower
            WHERE l.statutPenalite <> 'aucune'
            ORDER BY l.id DESC
        ";
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([self::class, 'fromRow'], $rows);
    }

    /** Sauvegarde (insert / update) */
    public function save(): void
    {
        $pdo = self::pdo();

        if ($this->id) {
            $sql = "
                UPDATE location
                SET 
                    dateLocation          = :dateLocation,
                    heureDebut            = :heureDebut,
                    heureFin              = :heureFin,
                    duree                 = :duree,
                    pt                    = :pt,
                    penalite              = :penalite,
                    statut                = :statut,
                    idAffectation         = :idAffectation,
                    idClient              = :idClient,
                    idAgent               = :idAgent,
                    statutPenalite        = :statutPenalite,
                    montantPenalitePaye   = :montantPenalitePaye,
                    datePaiementPenalite  = :datePaiementPenalite
                WHERE id = :id
            ";
        } else {
            $sql = "
                INSERT INTO location (
                    dateLocation, heureDebut, heureFin, duree, pt, penalite, statut,
                    idAffectation, idClient, idAgent,
                    statutPenalite, montantPenalitePaye, datePaiementPenalite
                ) VALUES (
                    :dateLocation, :heureDebut, :heureFin, :duree, :pt, :penalite, :statut,
                    :idAffectation, :idClient, :idAgent,
                    :statutPenalite, :montantPenalitePaye, :datePaiementPenalite
                )
            ";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'dateLocation'         => $this->dateLocation,
            'heureDebut'           => $this->heureDebut,
            'heureFin'             => $this->heureFin,
            'duree'                => $this->duree,
            'pt'                   => $this->pt,
            'penalite'             => $this->penalite,
            'statut'               => $this->statut,
            'idAffectation'        => $this->idAffectation,
            'idClient'             => $this->idClient,
            'idAgent'              => $this->idAgent,
            'statutPenalite'       => $this->statutPenalite ?? 'aucune',
            'montantPenalitePaye'  => $this->montantPenalitePaye ?? 0,
            'datePaiementPenalite' => $this->datePaiementPenalite,
            'id'                   => $this->id,
        ]);

        if (!$this->id) {
            $this->id = (int)$pdo->lastInsertId();
        }
    }

    /** Suppression */
    public static function delete(int $id): void
    {
        $pdo = self::pdo();
        $stmt = $pdo->prepare("DELETE FROM location WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}
