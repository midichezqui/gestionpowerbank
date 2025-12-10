<?php
require_once __DIR__ . '/core/Database.php';

try {
    $pdo = Database::getConnection();
    echo "<p>✅ Connexion réussie !</p>";

    // Exemple : lister les tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_NUM);

    echo "<h3>Tables trouvées :</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . htmlspecialchars($table[0]) . "</li>";
    }
    echo "</ul>";

} catch (PDOException $e) {
    echo "<p>❌ Erreur de connexion :</p>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
