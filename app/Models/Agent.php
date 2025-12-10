<?php
// app/Models/Agent.php

require_once __DIR__ . '/../../core/Database.php';

class Agent
{
    public $id;
    public $nom;
    public $postnom;
    public $prenom;
    public $sexe;
    public $telephone;
    public $adresse;
    public $email;
    public $photo;
    public $pseudo;
    public $pwd;
    public $idFonction;

    public $fonctionLabel;

    public function __construct(array $data = [])
    {
        $this->id        = $data['id'] ?? null;
        $this->nom       = $data['nom'] ?? '';
        $this->postnom   = $data['postnom'] ?? '';
        $this->prenom    = $data['prenom'] ?? '';
        $this->sexe      = $data['sexe'] ?? '';
        $this->telephone = $data['telephone'] ?? '';
        $this->adresse   = $data['adresse'] ?? '';
        $this->email     = $data['email'] ?? '';
        $this->photo     = $data['photo'] ?? '';
        $this->pseudo    = $data['pseudo'] ?? '';
        $this->pwd       = $data['pwd'] ?? '';
        $this->idFonction = $data['idFonction'] ?? null;

        $this->fonctionLabel = $data['fonctionLabel'] ?? null;
    }

    public static function all(): array
    {
        $pdo = Database::getConnection();
        $sql = "
            SELECT a.*, f.libelFonction AS fonctionLabel
            FROM agent a
            LEFT JOIN fonctions f ON f.id = a.idFonction
            ORDER BY a.id DESC
        ";

        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Agent($row), $rows);
    }

    public static function find(int $id): ?Agent
    {
        $pdo = Database::getConnection();
        $sql = "
            SELECT a.*, f.libelFonction AS fonctionLabel
            FROM agent a
            LEFT JOIN fonctions f ON f.id = a.idFonction
            WHERE a.id = :id LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Agent($row) : null;
    }

    public function save(): bool
    {
        $pdo = Database::getConnection();

        if ($this->id) {
            // UPDATE
            $stmt = $pdo->prepare("
                UPDATE agent
                SET nom = :nom,
                    postnom = :postnom,
                    prenom = :prenom,
                    sexe = :sexe,
                    telephone = :telephone,
                    adresse = :adresse,
                    email = :email,
                    photo = :photo,
                    pseudo = :pseudo,
                    pwd = :pwd,
                    idFonction = :idFonction
                WHERE id = :id
            ");

            return $stmt->execute([
                'nom'        => $this->nom,
                'postnom'    => $this->postnom,
                'prenom'     => $this->prenom,
                'sexe'       => $this->sexe,
                'telephone'  => $this->telephone,
                'adresse'    => $this->adresse,
                'email'      => $this->email,
                'photo'      => $this->photo,
                'pseudo'     => $this->pseudo,
                'pwd'        => $this->pwd,
                'idFonction' => $this->idFonction,
                'id'         => $this->id
            ]);
        }

        // INSERT
        $stmt = $pdo->prepare("
            INSERT INTO agent
            (nom, postnom, prenom, sexe, telephone, adresse, email, photo, pseudo, pwd, idFonction)
            VALUES
            (:nom, :postnom, :prenom, :sexe, :telephone, :adresse, :email, :photo, :pseudo, :pwd, :idFonction)
        ");

        $ok = $stmt->execute([
            'nom'        => $this->nom,
            'postnom'    => $this->postnom,
            'prenom'     => $this->prenom,
            'sexe'       => $this->sexe,
            'telephone'  => $this->telephone,
            'adresse'    => $this->adresse,
            'email'      => $this->email,
            'photo'      => $this->photo,
            'pseudo'     => $this->pseudo,
            'pwd'        => $this->pwd,
            'idFonction' => $this->idFonction,
        ]);

        if ($ok) {
            $this->id = $pdo->lastInsertId();
        }
        return $ok;
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM agent WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
