<?php
// app/Models/Client.php

require_once __DIR__ . '/../../core/Database.php';

class Client
{
    public $id;
    public $nom;
    public $postnom;
    public $prenom;
    public $sexe;
    public $adresse;
    public $idQuartier;
    public $telephone;
    public $personneContact;
    public $idEtat;

    // Libellés pour l'affichage
    public $quartierLabel;
    public $communeLabel;
    public $etatLabel;

    public function __construct(array $data = [])
    {
        $this->id              = $data['id'] ?? null;
        $this->nom             = $data['nom'] ?? null;
        $this->postnom         = $data['postnom'] ?? null;
        $this->prenom          = $data['prenom'] ?? null;
        $this->sexe            = $data['sexe'] ?? null;
        $this->adresse         = $data['adresse'] ?? null;
        $this->idQuartier      = $data['idQuartier'] ?? null;
        $this->telephone       = $data['telephone'] ?? null;
        $this->personneContact = $data['personneContact'] ?? null;
        $this->idEtat          = $data['idEtat'] ?? null;

        $this->quartierLabel   = $data['quartierLabel'] ?? null;
        $this->communeLabel    = $data['communeLabel'] ?? null;
        $this->etatLabel       = $data['etatLabel'] ?? null;
    }

    /**
     * Tous les clients avec libellés quartier + état
     */
    public static function all(): array
    {
        $pdo = Database::getConnection();

        $sql = "
            SELECT 
                c.*,
                q.nomQuartier AS quartierLabel,
                co.nomCommune AS communeLabel,
                e.libelEtat  AS etatLabel
            FROM clients c
            LEFT JOIN quartier q ON q.id = c.idQuartier
            LEFT JOIN commune co ON co.id = q.idCommune
            LEFT JOIN etat e     ON e.id = c.idEtat
            ORDER BY c.id DESC
        ";

        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Client($row), $rows);
    }

    /**
     * Trouver un client par ID
     */
    public static function find(int $id): ?Client
    {
        $pdo = Database::getConnection();

        $sql = "
            SELECT 
                c.*,
                q.nomQuartier AS quartierLabel,
                co.nomCommune AS communeLabel,
                e.libelEtat     AS etatLabel
            FROM clients c
            LEFT JOIN quartier q ON q.id = c.idQuartier
            LEFT JOIN commune co ON co.id = q.idCommune
            LEFT JOIN etat e     ON e.id = c.idEtat
            WHERE c.id = :id
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Client($row) : null;
    }

    /**
     * Insert ou update
     */
    public function save(): bool
    {
        $pdo = Database::getConnection();

        if ($this->id) {
            // UPDATE
            $sql = "
                UPDATE clients SET
                    nom             = :nom,
                    postnom         = :postnom,
                    prenom          = :prenom,
                    sexe            = :sexe,
                    adresse         = :adresse,
                    idQuartier      = :idQuartier,
                    telephone       = :telephone,
                    personneContact = :personneContact,
                    idEtat          = :idEtat
                WHERE id = :id
            ";

            $stmt = $pdo->prepare($sql);

            return $stmt->execute([
                'nom'             => $this->nom,
                'postnom'         => $this->postnom,
                'prenom'          => $this->prenom,
                'sexe'            => $this->sexe,
                'adresse'         => $this->adresse,
                'idQuartier'      => $this->idQuartier,
                'telephone'       => $this->telephone,
                'personneContact' => $this->personneContact,
                'idEtat'          => $this->idEtat,
                'id'              => $this->id,
            ]);
        } else {
            // INSERT
            $sql = "
                INSERT INTO clients (
                    nom, postnom, prenom, sexe, adresse, idQuartier, 
                    telephone, personneContact, idEtat
                ) VALUES (
                    :nom, :postnom, :prenom, :sexe, :adresse, :idQuartier,
                    :telephone, :personneContact, :idEtat
                )
            ";

            $stmt = $pdo->prepare($sql);

            $ok = $stmt->execute([
                'nom'             => $this->nom,
                'postnom'         => $this->postnom,
                'prenom'          => $this->prenom,
                'sexe'            => $this->sexe,
                'adresse'         => $this->adresse,
                'idQuartier'      => $this->idQuartier,
                'telephone'       => $this->telephone,
                'personneContact' => $this->personneContact,
                'idEtat'          => $this->idEtat,
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
        $stmt = $pdo->prepare("DELETE FROM clients WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
