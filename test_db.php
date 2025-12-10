<?php
require_once __DIR__ . '/core/Database.php';

try {
    $pdo = Database::getConnection();
    echo "<p>✅ Connexion réussie !</p>";

    // Afficher quelques infos pour être sûr
    var_dump($pdo);
} catch (PDOException $e) {
    echo "<p>❌ Erreur de connexion :</p>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
