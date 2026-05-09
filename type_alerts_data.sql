-- Création de la table type_alerts si elle n'existe pas
CREATE TABLE IF NOT EXISTS `type_alerts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `statut` int(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Insertion des données de base pour les types d'alertes
INSERT IGNORE INTO `type_alerts` (`id`, `libelle`, `description`, `statut`, `created_at`, `updated_at`) VALUES
(1, 'Contrôle technique', 'Alerte pour le contrôle technique du véhicule', 1, NOW(), NOW()),
(2, 'Assurance', 'Alerte pour le renouvellement de l\'assurance', 1, NOW(), NOW()),
(3, 'Révision', 'Alerte pour la révision du véhicule', 1, NOW(), NOW()),
(4, 'Vidange', 'Alerte pour la vidange du véhicule', 1, NOW(), NOW()),
(5, 'Pneumatiques', 'Alerte pour le changement des pneumatiques', 1, NOW(), NOW()),
(6, 'Permis de conduire', 'Alerte pour le renouvellement du permis de conduire', 1, NOW(), NOW()),
(7, 'Carte grise', 'Alerte pour le renouvellement de la carte grise', 1, NOW(), NOW()),
(8, 'Visite technique', 'Alerte pour la visite technique', 1, NOW(), NOW());
