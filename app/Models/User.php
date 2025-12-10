<?php
// app/Models/User.php

require_once __DIR__ . '/../../core/Database.php';

class User
{
    public ?int $id = null;
    public string $nom;
    public string $postnom;
    public string $prenom;
    public string $sexe;
    public string $telephone;
    public string $adresse;
    public string $email;
    public string $photo;
    public string $pseudo;
    public string $pwd;        // mot de passe (idéalement HASHÉ)
    public int $idFonction;

    /**
     * Hydrater un User à partir d'un tableau (ligne de la BD)
     */
    public static function fromArray(array $data): User
    {
        $user = new self();

        $user->id        = isset($data['id']) ? (int) $data['id'] : null;
        $user->nom       = $data['nom'] ?? '';
        $user->postnom   = $data['postnom'] ?? '';
        $user->prenom    = $data['prenom'] ?? '';
        $user->sexe      = $data['sexe'] ?? '';
        $user->telephone = $data['telephone'] ?? '';
        $user->adresse   = $data['adresse'] ?? '';
        $user->email     = $data['email'] ?? '';
        $user->photo     = $data['photo'] ?? '';
        $user->pseudo    = $data['pseudo'] ?? '';
        $user->pwd       = $data['pwd'] ?? '';
        $user->idFonction = isset($data['idFonction']) ? (int) $data['idFonction'] : 0;

        return $user;
    }

    /**
     * Récupérer un agent (user) par son pseudo
     */
    public static function findByPseudo(string $pseudo): ?User
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("SELECT * FROM agent WHERE pseudo = :pseudo LIMIT 1");
        $stmt->execute([':pseudo' => $pseudo]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return self::fromArray($row);
    }

    /**
     * Vérifier les identifiants de connexion
     * Retourne l'objet User si OK, sinon null
     */
    public static function verifyCredentials(string $pseudo, string $password): ?User
    {
        $user = self::findByPseudo($pseudo);

        if (!$user) {
            return null;
        }

        // 🔐 CAS 1 : pwd contient un hash (recommandé)
        if (password_verify($password, $user->pwd)) {
            return $user;
        }

        // 🔓 CAS 2 (optionnel) : si pour l’instant tu stockes le mot de passe en clair
        // décommente la ligne suivante :
        
        if ($password === $user->pwd) {
            return $user;
        }
        

        return null;
    }

    /**
     * Enregistrer ou mettre à jour un agent
     */
    public function save(): bool
    {
        $pdo = Database::getConnection();

        if ($this->id === null) {
            // Insertion
            $stmt = $pdo->prepare("
                INSERT INTO agent 
                    (nom, postnom, prenom, sexe, telephone, adresse, email, photo, pseudo, pwd, idFonction)
                VALUES 
                    (:nom, :postnom, :prenom, :sexe, :telephone, :adresse, :email, :photo, :pseudo, :pwd, :idFonction)
            ");

            $ok = $stmt->execute([
                ':nom'        => $this->nom,
                ':postnom'    => $this->postnom,
                ':prenom'     => $this->prenom,
                ':sexe'       => $this->sexe,
                ':telephone'  => $this->telephone,
                ':adresse'    => $this->adresse,
                ':email'      => $this->email,
                ':photo'      => $this->photo,
                ':pseudo'     => $this->pseudo,
                ':pwd'        => $this->pwd,
                ':idFonction' => $this->idFonction,
            ]);

            if ($ok) {
                $this->id = (int) $pdo->lastInsertId();
            }

            return $ok;
        }

        // Mise à jour
        $stmt = $pdo->prepare("
            UPDATE agent SET
                nom        = :nom,
                postnom    = :postnom,
                prenom     = :prenom,
                sexe       = :sexe,
                telephone  = :telephone,
                adresse    = :adresse,
                email      = :email,
                photo      = :photo,
                pseudo     = :pseudo,
                pwd        = :pwd,
                idFonction = :idFonction
            WHERE id = :id
        ");

        return $stmt->execute([
            ':nom'        => $this->nom,
            ':postnom'    => $this->postnom,
            ':prenom'     => $this->prenom,
            ':sexe'       => $this->sexe,
            ':telephone'  => $this->telephone,
            ':adresse'    => $this->adresse,
            ':email'      => $this->email,
            ':photo'      => $this->photo,
            ':pseudo'     => $this->pseudo,
            ':pwd'        => $this->pwd,
            ':idFonction' => $this->idFonction,
            ':id'         => $this->id,
        ]);
    }
}
