<?php
// app/Models/Fonction.php

require_once __DIR__ . '/../../core/Database.php';

class Fonction
{
    public $id;
    public $libelFonction;

    public function __construct(array $data = [])
    {
        $this->id            = $data['id'] ?? null;
        $this->libelFonction = $data['libelFonction'] ?? null;
    }

    /**
     * Récupérer toutes les fonctions
     */
    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM fonctions ORDER BY libelFonction ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Fonction($row), $rows);
    }

    /**
     * Trouver une fonction par ID
     */
    public static function find(int $id): ?Fonction
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM fonctions WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Fonction($row) : null;
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
                UPDATE fonctions
                SET libelFonction = :libelFonction
                WHERE id = :id
            ");

            return $stmt->execute([
                'libelFonction' => $this->libelFonction,
                'id'            => $this->id,
            ]);
        } else {
            // INSERT
            $stmt = $pdo->prepare("
                INSERT INTO fonctions (libelFonction)
                VALUES (:libelFonction)
            ");

            $ok = $stmt->execute([
                'libelFonction' => $this->libelFonction,
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
        $stmt = $pdo->prepare("DELETE FROM fonctions WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
