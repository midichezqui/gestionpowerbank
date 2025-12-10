<?php
// app/Models/Powerbank.php

require_once __DIR__ . '/../../core/Database.php';

class Powerbank
{
    public $id;
    public $codePower;
    public $dateAcquis;
    public $capacite;
    public $presentationEcran;
    public $idTypeCable;
    public $idStatut;
    public $tarif;

    // Optionnel : libellés issus de jointures
    public $typeCableLibel;
    public $statutLibel;
    public $idCommune;
    public $communeLibel;

    public function __construct(array $data = [])
    {
        $this->id                = $data['id'] ?? null;
        $this->codePower         = $data['codePower'] ?? '';
        $this->dateAcquis        = $data['dateAcquis'] ?? '';
        $this->capacite          = $data['capacite'] ?? '';
        $this->presentationEcran = $data['presentationEcran'] ?? '';
        $this->idTypeCable       = $data['idTypeCable'] ?? null;
        $this->idStatut          = $data['idStatut'] ?? null;
        $this->tarif             = $data['tarif'] ?? 0;

        $this->typeCableLibel    = $data['typeCableLibel'] ?? null;
        $this->statutLibel       = $data['statutLibel'] ?? null;
        $this->idCommune         = $data['idCommune'] ?? null;
        $this->communeLibel      = $data['communeLibel'] ?? null;
    }

    /**
     * Retourne tous les PowerBanks (avec libellés optionnels si jointure faite)
     */
    public static function all(): array
    {
        $pdo = Database::getConnection();

        // Si tu as les tables type_cable et statut, tu peux faire une jointure
        $sql = "
            SELECT 
                pb.id,
                pb.codePower,
                pb.dateAcquis,
                pb.capacite,
                pb.presentationEcran,
                pb.idTypeCable,
                pb.idStatut,
                pb.tarif,
                pb.idCommune,
                tc.libelType AS typeCableLibel,
                s.LibelStatut AS statutLibel,
                c.nomCommune AS communeLibel
            FROM power_bank pb
            LEFT JOIN type_cable tc ON tc.id = pb.idTypeCable
            LEFT JOIN statut s      ON s.id = pb.idStatut
            LEFT JOIN commune c     ON c.id = pb.idCommune
            ORDER BY pb.id DESC
        ";

        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = new self($row);
        }
        return $result;
    }

    public static function find(int $id): ?Powerbank
    {
        $pdo = Database::getConnection();

        $sql = "SELECT * FROM power_bank WHERE id = :id LIMIT 1";
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
                INSERT INTO power_bank (
                    codePower, dateAcquis, capacite,
                    presentationEcran, idTypeCable, idStatut, tarif, idCommune
                )
                VALUES (
                    :codePower, :dateAcquis, :capacite,
                    :presentationEcran, :idTypeCable, :idStatut, :tarif, :idCommune
                )
            ";
            $stmt = $pdo->prepare($sql);
            $ok = $stmt->execute([
                'codePower'         => $this->codePower,
                'dateAcquis'        => $this->dateAcquis,
                'capacite'          => $this->capacite,
                'presentationEcran' => $this->presentationEcran,
                'idTypeCable'       => $this->idTypeCable,
                'idStatut'          => $this->idStatut,
                'tarif'             => $this->tarif,
                'idCommune'         => $this->idCommune,
            ]);

            if ($ok) {
                $this->id = (int)$pdo->lastInsertId();
            }
            return $ok;

        } else {
            // UPDATE
            $sql = "
                UPDATE power_bank
                SET
                    codePower         = :codePower,
                    dateAcquis        = :dateAcquis,
                    capacite          = :capacite,
                    presentationEcran = :presentationEcran,
                    idTypeCable       = :idTypeCable,
                    idStatut          = :idStatut,
                    tarif             = :tarif,
                    idCommune         = :idCommune
                WHERE id = :id
            ";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                'codePower'         => $this->codePower,
                'dateAcquis'        => $this->dateAcquis,
                'capacite'          => $this->capacite,
                'presentationEcran' => $this->presentationEcran,
                'idTypeCable'       => $this->idTypeCable,
                'idStatut'          => $this->idStatut,
                'tarif'             => $this->tarif,
                'idCommune'         => $this->idCommune,
                'id'                => $this->id,
            ]);
        }
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();

        $sql = "DELETE FROM power_bank WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Met à jour le statut d'un PowerBank (idStatut)
     */
    public static function setStatut(int $idPower, int $newStatut): bool
    {
        $pdo = Database::getConnection();

        $sql = "UPDATE power_bank SET idStatut = :statut WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            'statut' => $newStatut,
            'id'     => $idPower,
        ]);
    }

    /**
     * Met tous les PowerBanks affectés mais jamais loués
     * sur une période donnée à l'état voulu (ici idStatut = 2).
     */
    public static function libererNonLoueParPeriode(string $date1, string $date2): int
    {
        $pdo = Database::getConnection();

        $sql = "
            UPDATE power_bank pb
            INNER JOIN affectation a ON a.idPower = pb.id
            LEFT JOIN location l ON l.idAffectation = a.id
            SET pb.idStatut = 2
            WHERE l.id IS NULL
              AND DATE(a.dateAffectation) BETWEEN :d1 AND :d2
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'd1' => $date1,
            'd2' => $date2,
        ]);

        return $stmt->rowCount();
    }

    /**
     * Pour remplir les <select> type câble
     */
    public static function getTypesCable(): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT id, libelType FROM type_cable ORDER BY libelType";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Pour remplir les <select> statut
     */
    public static function getStatuts(): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT id, LibelStatut FROM statut ORDER BY LibelStatut";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
