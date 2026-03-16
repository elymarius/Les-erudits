-- ============================================================
--  À exécuter dans phpMyAdmin de l'étudiant 4
--  http://192.168.1.53/phpmyadmin
--  Cliquer sur "auth_system" → onglet "SQL" → coller → Exécuter
-- ============================================================

USE auth_system;

CREATE TABLE IF NOT EXISTS historique_energie (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    analog1       FLOAT    NOT NULL  COMMENT 'Valeur éolienne lue depuis IPX800 (kWh)',
    moyenne_jour  FLOAT    NOT NULL  COMMENT 'Moyenne journalière calculée au moment de l enregistrement',
    date_mesure   DATE     NOT NULL  COMMENT 'Date de la mesure (YYYY-MM-DD)',
    heure_mesure  TIME     NOT NULL  COMMENT 'Heure de la mesure (HH:MM:SS)',
    horodatage    DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Inséré automatiquement par le serveur'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Vérification
DESCRIBE historique_energie;
