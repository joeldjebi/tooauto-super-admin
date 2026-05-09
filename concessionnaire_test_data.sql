-- Script pour insérer des données de test pour les tables liées aux demandes concessionnaires

-- Table type_de_demandes
CREATE TABLE IF NOT EXISTS `type_de_demandes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `statut` int(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

INSERT IGNORE INTO `type_de_demandes` (`id`, `libelle`, `description`) VALUES
(1, 'Achat véhicule neuf', 'Demande d\'achat d\'un véhicule neuf'),
(2, 'Achat véhicule d\'occasion', 'Demande d\'achat d\'un véhicule d\'occasion'),
(3, 'Location véhicule', 'Demande de location de véhicule'),
(4, 'Financement véhicule', 'Demande de financement pour l\'achat d\'un véhicule'),
(5, 'Échange véhicule', 'Demande d\'échange de véhicule'),
(6, 'Estimation véhicule', 'Demande d\'estimation de la valeur d\'un véhicule');

-- Table type_de_vehicules
CREATE TABLE IF NOT EXISTS `type_de_vehicules` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `statut` int(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

INSERT IGNORE INTO `type_de_vehicules` (`id`, `libelle`, `description`) VALUES
(1, 'Berline', 'Véhicule de type berline'),
(2, 'SUV', 'Véhicule utilitaire sport'),
(3, 'Break', 'Véhicule de type break'),
(4, 'Coupé', 'Véhicule de type coupé'),
(5, 'Cabriolet', 'Véhicule décapotable'),
(6, 'Monospace', 'Véhicule monospace'),
(7, 'Pick-up', 'Véhicule pick-up'),
(8, 'Camionnette', 'Véhicule utilitaire léger'),
(9, 'Moto', 'Deux-roues motorisé'),
(10, 'Scooter', 'Deux-roues motorisé léger');

-- Table marques
CREATE TABLE IF NOT EXISTS `marques` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `statut` int(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

INSERT IGNORE INTO `marques` (`id`, `libelle`, `description`) VALUES
(1, 'Toyota', 'Marque automobile japonaise'),
(2, 'Peugeot', 'Marque automobile française'),
(3, 'Renault', 'Marque automobile française'),
(4, 'BMW', 'Marque automobile allemande'),
(5, 'Mercedes-Benz', 'Marque automobile allemande'),
(6, 'Audi', 'Marque automobile allemande'),
(7, 'Volkswagen', 'Marque automobile allemande'),
(8, 'Ford', 'Marque automobile américaine'),
(9, 'Nissan', 'Marque automobile japonaise'),
(10, 'Hyundai', 'Marque automobile coréenne'),
(11, 'Kia', 'Marque automobile coréenne'),
(12, 'Honda', 'Marque automobile japonaise'),
(13, 'Mazda', 'Marque automobile japonaise'),
(14, 'Suzuki', 'Marque automobile japonaise'),
(15, 'Mitsubishi', 'Marque automobile japonaise');

-- Table concessionnaires
CREATE TABLE IF NOT EXISTS `concessionnaires` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `statut` int(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

INSERT IGNORE INTO `concessionnaires` (`id`, `name`, `email`, `telephone`, `adresse`) VALUES
(1, 'Auto Plus Abidjan', 'contact@autoplus.ci', '+225 20 30 40 50', 'Cocody, Abidjan'),
(2, 'Car Center Yopougon', 'info@carcenter.ci', '+225 20 30 40 51', 'Yopougon, Abidjan'),
(3, 'Auto Express Marcory', 'contact@autoexpress.ci', '+225 20 30 40 52', 'Marcory, Abidjan'),
(4, 'Premium Motors', 'sales@premiummotors.ci', '+225 20 30 40 53', 'Plateau, Abidjan'),
(5, 'City Auto', 'info@cityauto.ci', '+225 20 30 40 54', 'Adjamé, Abidjan'),
(6, 'Auto World', 'contact@autoworld.ci', '+225 20 30 40 55', 'Koumassi, Abidjan'),
(7, 'Speed Motors', 'sales@speedmotors.ci', '+225 20 30 40 56', 'Treichville, Abidjan'),
(8, 'Elite Cars', 'info@elitecars.ci', '+225 20 30 40 57', 'Cocody, Abidjan');
