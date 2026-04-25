<?php
// ============================================================
// NoubaTN — connexion.php
// Connexion à la base de données MySQL via PDO
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'noubatn');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Retourne une instance PDO connectée à la base nouba_tn.
 * En cas d'échec, affiche un message d'erreur et arrête le script.
 *
 * @return PDO
 */
function getConnexion(): PDO {
    $dsn = 'mysql:host=' . DB_HOST
         . ';dbname=' . DB_NAME
         . ';charset=' . DB_CHARSET;

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // lance des exceptions en cas d'erreur
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // retourne des tableaux associatifs par défaut
        PDO::ATTR_EMULATE_PREPARES   => false,                     // prépared statements natifs
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // En production, ne jamais afficher le message brut
        die('<p style="color:red;font-family:sans-serif;">
              ❌ Erreur de connexion à la base de données : '
              . htmlspecialchars($e->getMessage()) .
             '</p>');
    }
}
?>
