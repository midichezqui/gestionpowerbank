<?php
// app/Models/Quartier.php

require_once __DIR__ . '/../../core/Database.php';

class Quartier
{
    public $id;
    public $nomQuartier;
    public $idCommune;

    public $communeLabel;

    public function __construct(array $data = [])
    {
        $this->id           = $data['id'] ?? null;
        $this->nomQuartier  = $data['nomQuartier'] ?? null;
        $this->idCommune    = $data['idCommune'] ?? null;

        $this->communeLabel = $data['communeLabel'] ?? null;
    }

    /**
     * Récupérer tous les quartiers avec leur commune
     */
    public static function all(): array
    {
        $pdo = Database::getConnection();

        $sql = "
            SELECT 
                q.*,
                c.nomCommune AS communeLabel
            FROM quartier q
            LEFT JOIN commune c ON c.id = q.idCommune
            ORDER BY q.id DESC
        ";

        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Quartier($row), $rows);
    }

    /**
     * Trouver un quartier par ID
     */
    public static function find(int $id): ?Quartier
    {
        $pdo = Database::getConnection();

        $sql = "
            SELECT 
                q.*,
                c.nomCommune AS communeLabel
            FROM quartier q
            LEFT JOIN commune c ON c.id = q.idCommune
            WHERE q.id = :id
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Quartier($row) : null;
    }

    /**
     * Enregistrer (insert / update)
     */
    public function save(): bool
    {
        $pdo = Database::getConnection();

        if ($this->id) {
            // UPDATE
            $sql = "
                UPDATE quartier
                SET nomQuartier = :nomQuartier,
                    idCommune   = :idCommune
                WHERE id = :id
            ";

            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                'nomQuartier' => $this->nomQuartier,
                'idCommune'   => $this->idCommune,
                'id'          => $this->id
            ]);
        } else {
            // INSERT
            $sql = "
                INSERT INTO quartier (nomQuartier, idCommune)
                VALUES (:nomQuartier, :idCommune)
            ";

            $stmt = $pdo->prepare($sql);

            $ok = $stmt->execute([
                'nomQuartier' => $this->nomQuartier,
                'idCommune'   => $this->idCommune,
            ]);

            if ($ok) {
                $this->id = (int)$pdo->lastInsertId();
            }

            return $ok;
        }
    }

    /**
     * Supprimer
     */
    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM quartier WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
