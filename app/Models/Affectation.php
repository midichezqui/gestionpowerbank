<?php
// app/Models/Affectation.php

require_once __DIR__ . '/../../core/Database.php';

class Affectation
{
    public $id;
    public $dateAffectation;
    public $idAgent;
    public $idPower;
    public $idAgentCreate;

    // Champs décorés via jointures
    public $agentNom;        // agent affecté
    public $agentCreateNom;  // agent créateur (pour super admin)
    public $quartierNom;
    public $powerCode;

    public function __construct(array $data = [])
    {
        $this->id              = $data['id'] ?? null;
        $this->dateAffectation = $data['dateAffectation'] ?? null;
        $this->idAgent         = $data['idAgent'] ?? null;
        $this->idPower         = $data['idPower'] ?? null;
        $this->idAgentCreate   = $data['idAgentCreate'] ?? null;

        $this->agentNom        = $data['agentNom']       ?? null;
        $this->agentCreateNom  = $data['agentCreateNom'] ?? null;
        $this->quartierNom     = $data['quartierNom']    ?? null;
        $this->powerCode       = $data['powerCode']      ?? null;
    }

    /**
     * 🔹 Toutes les affectations (pour Super Admin)
     */
    public static function allWithJoins(): array
    {
        $pdo = Database::getConnection();

        $sql = "
            SELECT
                a.id,
                a.dateAffectation,
                a.idAgent,
                a.idPower,
                a.idAgentCreate,
                CONCAT(ag.prenom, ' ', ag.nom, ' ', ag.postnom)    AS agentNom,
                CONCAT(agc.prenom, ' ', agc.nom, ' ', agc.postnom) AS agentCreateNom,
                pb.codePower  AS powerCode
            FROM affectation a
            INNER JOIN agent      ag  ON ag.id  = a.idAgent
            LEFT JOIN agent       agc ON agc.id = a.idAgentCreate
            INNER JOIN power_bank pb  ON pb.id  = a.idPower
            ORDER BY a.dateAffectation DESC, a.id DESC
        ";

        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = new self($row);
        }
        return $result;
    }

    /**
     * 🔹 Affectations créées par un agent donné (Admin)
     */
    public static function allCreatedByAgent(int $idAgentCreate): array
    {
        $pdo = Database::getConnection();

        $sql = "
            SELECT
                a.id,
                a.dateAffectation,
                a.idAgent,
                a.idPower,
                a.idAgentCreate,
                CONCAT(ag.prenom, ' ', ag.nom, ' ', ag.postnom) AS agentNom,
                pb.codePower  AS powerCode
            FROM affectation a
            INNER JOIN agent      ag  ON ag.id  = a.idAgent
            INNER JOIN power_bank pb  ON pb.id  = a.idPower
            WHERE a.idAgentCreate = :idAgentCreate
            ORDER BY a.dateAffectation DESC, a.id DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['idAgentCreate' => $idAgentCreate]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = new self($row);
        }
        return $result;
    }

    /**
     * 🔹 Affectations d'aujourd'hui assignées à un agent simple
     * (idAgent = agent connecté ET dateAffectation = aujourd'hui)
     */
    public static function allForAgentToday(int $idAgent): array
    {
        $pdo = Database::getConnection();

        $sql = "
            SELECT
                a.id,
                a.dateAffectation,
                a.idAgent,
                a.idPower,
                a.idAgentCreate,
                CONCAT(ag.prenom, ' ', ag.nom, ' ', ag.postnom) AS agentNom,
                pb.codePower  AS powerCode
            FROM affectation a
            INNER JOIN agent      ag  ON ag.id  = a.idAgent
            INNER JOIN power_bank pb  ON pb.id  = a.idPower
            WHERE a.idAgent = :idAgent
              AND DATE(a.dateAffectation) = CURDATE()
            ORDER BY a.dateAffectation DESC, a.id DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['idAgent' => $idAgent]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = new self($row);
        }
        return $result;
    }

    public static function find(int $id): ?Affectation
    {
        $pdo = Database::getConnection();

        $sql = "SELECT * FROM affectation WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new self($row);
    }

    public function save(): bool
    {
        $pdo = Database::getConnection();

        if ($this->id === null) {
            // INSERT
            $sql = "
                INSERT INTO affectation (
                    dateAffectation,
                    idAgent,
                    idPower,
                    idAgentCreate
                ) VALUES (
                    :dateAffectation,
                    :idAgent,
                    :idPower,
                    :idAgentCreate
                )
            ";

            $stmt = $pdo->prepare($sql);
            $ok = $stmt->execute([
                'dateAffectation' => $this->dateAffectation,
                'idAgent'         => $this->idAgent,
                'idPower'         => $this->idPower,
                'idAgentCreate'   => $this->idAgentCreate,
            ]);

            if ($ok) {
                $this->id = (int)$pdo->lastInsertId();
            }
            return $ok;

        } else {
            // UPDATE simple
            $sql = "
                UPDATE affectation
                SET
                    dateAffectation = :dateAffectation,
                    idAgent         = :idAgent,
                    idPower         = :idPower,
                    idAgentCreate   = :idAgentCreate
                WHERE id = :id
            ";

            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                'dateAffectation' => $this->dateAffectation,
                'idAgent'         => $this->idAgent,
                'idPower'         => $this->idPower,
                'idAgentCreate'   => $this->idAgentCreate,
                'id'              => $this->id,
            ]);
        }
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();

        $sql = "DELETE FROM affectation WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
