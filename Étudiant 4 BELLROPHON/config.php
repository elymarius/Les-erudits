<?php
// =============================================
//  config.php — Connexion PDO à la base MySQL
// =============================================

define('DB_HOST',    '127.0.0.1');
define('DB_PORT',    '3306');
define('DB_NAME',    'auth_system');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Affiche l'erreur réelle pour aider au diagnostic
    // ⚠️ À désactiver une fois la connexion fonctionnelle
    die("<pre style='color:red;font-family:monospace;padding:20px;'>
<strong>❌ Erreur de connexion à la base de données :</strong>

" . htmlspecialchars($e->getMessage()) . "

<strong>Détails :</strong>
- Hôte    : " . DB_HOST . ":" . DB_PORT . "
- Base    : " . DB_NAME . "
- Utilisateur : " . DB_USER . "
</pre>");
}
