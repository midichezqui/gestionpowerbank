<?php
// app/Models/Commune.php

require_once __DIR__ . '/../../core/Database.php';

class Commune
{
    public $id;
    public $nomCommune;

    public function __construct(array $data = [])
    {
        $this->id         = $data['id'] ?? null;
        $this->nomCommune = $data['nomCommune'] ?? null;
    }

    /**
     * Récupérer toutes les communes
     */
    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM commune ORDER BY nomCommune ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Commune($row), $rows);
    }

    /**
     * Trouver une commune
     */
    public static function find(int $id): ?Commune
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM commune WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Commune($row) : null;
    }

    /**
     * Enregistrer (insert / update)
     */
    public function save(): bool
    {
        $pdo = Database::getConnection();

        if ($this->id) {
            // UPDATE
            $stmt = $pdo->prepare("
                UPDATE commune
                SET nomCommune = :nomCommune
                WHERE id = :id
            ");
            return $stmt->execute([
                'nomCommune' => $this->nomCommune,
                'id'         => $this->id
            ]);
        } else {
            // INSERT
            $stmt = $pdo->prepare("
                INSERT INTO commune (nomCommune)
                VALUES (:nomCommune)
            ");
            $ok = $stmt->execute([
                'nomCommune' => $this->nomCommune
            ]);

            if ($ok) {
                $this->id = $pdo->lastInsertId();
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
        $stmt = $pdo->prepare("DELETE FROM commune WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
