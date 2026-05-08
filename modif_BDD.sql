-- =============================================================================
-- 2026_mrbs_99_v2.sql — Script SQL complementaire pour la V2 du Digicode
-- A executer APRES avoir importe la base 2026_mrbs_99.sql (V1)
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 1. Creation de la table mrbs_room_digicode
-- Note : l'id n'est PAS AUTO_INCREMENT (il reprend l'id de mrbs_room)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mrbs_room_digicode` (
    `id`       INT(11)     NOT NULL,
    `digicode` VARCHAR(6)  CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_room_digicode`
        FOREIGN KEY (`id`) REFERENCES `mrbs_room` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 2. Insertion d'un digicode aleatoire pour chaque salle existante
-- (ils seront regeneres via l'interface admin ou l'evenement programme)
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `mrbs_room_digicode` (`id`, `digicode`)
SELECT `id`, UPPER(SUBSTRING(MD5(RAND()), 1, 6)) FROM `mrbs_room`;

-- -----------------------------------------------------------------------------
-- 3. Activation du scheduler d'evenements MySQL
-- Dans phpMyAdmin : Variables > event_scheduler > ON
-- Ou via la commande : SET GLOBAL event_scheduler = ON;
-- (necessite les droits SUPER)
-- -----------------------------------------------------------------------------

-- -----------------------------------------------------------------------------
-- 4. Evenement programme : regeneration automatique des digicodes le vendredi a 23h
-- -----------------------------------------------------------------------------
DROP EVENT IF EXISTS `evt_regenerer_digicodes`;

CREATE EVENT `evt_regenerer_digicodes`
    ON SCHEDULE EVERY 1 WEEK
    STARTS (
        TIMESTAMP(CURDATE())
        + INTERVAL (6 - WEEKDAY(CURDATE()) + 7) % 7 DAY
        + INTERVAL 23 HOUR
    )
    DO
    UPDATE `mrbs_room_digicode`
    SET `digicode` = UPPER(SUBSTRING(MD5(RAND()), 1, 6));

-- FIN DU SCRIPT
